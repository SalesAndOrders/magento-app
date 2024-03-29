<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Integration\Model\IntegrationFactory;
use Magento\Store\Model\StoreManagerInterface;
use \SalesAndOrders\FeedTool\Model\Integration\Activation as IntegrationActivation;
use \SalesAndOrders\FeedTool\Model\Transport;
use SalesAndOrders\FeedTool\Model\Cache;

/**
 * Class Webhook Resource Model
 */
class WebHook extends AbstractDb
{
    /**
     * @var string
     */
    protected $_mainTable = 'perspective_webhooks';
    /**
     * @var IntegrationFactory
     */
    protected $integrationFactory;
    /**
     * @var \Magento\Integration\Model\Integration
     */
    protected $integration;
    /**
     * @var mixed|null
     */
    protected $integrationWebhook = null;

    protected $storeManager;

    protected $transport;

    protected $cacheModel;

    /**
     * WebHook constructor.
     *
     * @param Context            $context
     * @param IntegrationFactory $integrationFactory
     */
    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        StoreManagerInterface $storeManager,
        Transport $transport,
        Cache $cacheModel
    ) {
        $this->integrationFactory = $integrationFactory;
        $this->storeManager = $storeManager;
        $this->transport = $transport;
        $this->cacheModel = $cacheModel;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('perspective_webhooks', 'id');
    }

    /**
     * @return \Magento\Integration\Model\Integration
     */
    public function getIntegration()
    {
        if (!$this->integration || !$this->integration->getId()) {
            $this->integration = $this->integrationFactory->create()
                ->load(IntegrationActivation::INTEGRATION_NAME, 'name');
        }
        $integration = $this->integration;
        return $integration;
    }

    /**
     * @param  array $insertData
     * @param  int   $authorize_flag
     * @return bool
     */
    public function addIntegrationWebHook($insertData = [], $authorize_flag = 0)
    {
        $this->integration = $this->getIntegration();
        if ($this->integration && $this->integration->getId()) {
            $insertData['store_code'] = isset($insertData['store_code']) ? $insertData['store_code'] : '';
            $initial = !empty($insertData['initial']);
            unset($insertData['initial']);
            $integrationWebHook = $this->getWebHookData(
                $this->integration->getId(),
                $insertData['store_code'],
                $initial
            );
            $insertData += [
                'integration_id' => $this->integration->getId(),
                'consumer_id' => $this->integration->getConsumerId(),
                'is_oath_authorized' => $authorize_flag
            ];

            if (!isset($insertData['verify_url_endpoint'])) {
                $insertData['verify_url_endpoint'] =
                    $this->getVerifyUrlEndpointByIntegrationId($this->integration->getId());
            }

            if (!$integrationWebHook) {
                $this->getConnection()->insert($this->getMainTable(), $insertData);
            } else {
                $store_code = $initial ? '' : $insertData['store_code'];
                $where[] = $this->getConnection()->quoteInto('integration_id = ?', $this->integration->getId());
                $where[] = $this->getConnection()->quoteInto('store_code = ?', $store_code);
                $this->getConnection()->update($this->getMainTable(), $insertData, $where);
            }

            if ($authorize_flag == 1) {
                $this->cacheModel->cleanCahes(['config', 'block_html']);
            }
            return true;
        }
        return false;
    }

    /**
     * @param int $integrationId
     * @param string $store_code
     * @param bool $initial
     * @return mixed
     */
    public function getWebHookData($integrationId = 0, $store_code = '', $initial = false)
    {
        $store_code = $initial ? '' : $store_code;
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where('integration_id = ?', $integrationId)
            ->where('store_code = ?', $store_code);

        return $this->getConnection()->query($select)->fetchObject();
    }

    /**
     * @param int $integrationId
     * @return mixed
     */
    public function getCustomWebHookData($integrationId = 0)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where('integration_id = ?', $integrationId);

        return $this->getConnection()->query($select)->fetchObject();
    }

    /**
     * @param $integrationId
     * @return string
     */
    public function getVerifyUrlEndpointByIntegrationId($integrationId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where('integration_id = ?', $integrationId)
            ->where('verify_url_endpoint IS NOT NULL OR verify_url_endpoint != ?', '');
        $result = $this->getConnection()->query($select)->fetchObject();
        return empty($result->verify_url_endpoint) ? '' : $result->verify_url_endpoint;
    }

    /**
     * @param int $integrationId
     */
    public function deleteWebHook($integrationId = 0)
    {
        $this->getConnection()->delete($this->getMainTable(), ['integration_id = ?' => $integrationId]);
        return true;
    }

    /**
     * @param int $integrationId
     * @param string $store_code
     * @return bool
     */
    public function deleteWebHookByStoreCode($integrationId = 0, $store_code = '')
    {
        $where[] = $this->getConnection()->quoteInto('integration_id = ?', $integrationId);
        $where[] = $this->getConnection()->quoteInto('store_code = ?', $store_code);
        $this->getConnection()->delete($this->getMainTable(), $where);
        return true;
    }

    /**
     * @param int $integrationId
     * @return bool
     */
    public function deleteWebHookByIntegration($integrationId = 0)
    {
        $where[] = $this->getConnection()->quoteInto('integration_id = ?', $integrationId);
        $this->getConnection()->delete($this->getMainTable(), $where);
        return true;
    }
    /**
     * Removes integration for All stores
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteWebHookAllIntegrations(): int
    {
        $where = [];// = $this->getConnection()->quoteInto('integration_id = ?', '');
        $res = $this->getConnection()->delete($this->getMainTable(), $where);
        return $res;
    }

    /**
     * Removes integration per store_code
     * @param string $store_code
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteWebHookStoreIntegrations(string $store_code): int
    {
        $where = $this->getConnection()->quoteInto('store_code = ?', $store_code);
         $res = $this->getConnection()->delete($this->getMainTable(), $where);
        return $res;
    }

    /**
     * @param null $store_code
     * @return bool
     */
    public function uninstall($store_code = null)
    {
        $integration = $this->getIntegration();
        $webHook = $this->getWebHookData($this->integration->getId(), $store_code);
        $result = false;
        if ($webHook) {
            // send to uninstall url data
            $decodeResult = null;
            $storeBaseUrl = $this->storeManager->getStore()->getBaseUrl();
            $data = [
                'store_base_url' => $storeBaseUrl,
                'store_code' => $store_code
            ];
            if ($webHook && $webHook->uninstall_url) {
                $response = $this->transport->sendData($webHook->uninstall_url, $data);
                $decodeResult = json_decode($response['response']);
            }

            // remove webhook from DB
            $this->deleteWebHookByStoreCode($this->integration->getId(), $store_code);
            $result = true;
            /**
             * delete integration if we dont have any webhooks
             */
            $enabledWebhooks = $this->getEnabledWebhooks();
            if (!$enabledWebhooks || $enabledWebhooks->webhook_count == 0) {
                $this->integration->delete();
            }
            /**
             * we cleared cache if no have is_authorized webhooks
             */
            $authWebHooks = $this->getAuthorizedWebhooks();
            if (!$authWebHooks || $authWebHooks->webhook_count == 0) {
                $this->cacheModel->cleanCahes(['config', 'block_html']);
            }
        }

        return $result;
    }

    /**
     * @param null $integrationID
     * @return bool
     */
    public function uninstallAll($integrationID = null)
    {
        $webHook = $this->getWebHookWithUninstallUrl($integrationID);
        // remove webhooks by Int ID
        $this->deleteWebHookByIntegration($integrationID);
        // send curl
        $decodeResult = null;
        $storeBaseUrl = $this->storeManager->getStore()->getBaseUrl();
        $store_code = $this->storeManager->getStore()->getCode();
        $data = [
            'store_base_url' => $storeBaseUrl
        ];
        if ($webHook && $webHook->uninstall_url) {
            $response = $this->transport->sendData($webHook->uninstall_url, $data);
            $decodeResult = json_decode($response['response']);
        }
        // clear caches
        $authWebHooks = $this->getAuthorizedWebhooks();
        if (!$authWebHooks || $authWebHooks->webhook_count == 0) {
            $this->cacheModel->cleanCahes(['config', 'block_html']);
        }
        return true;
    }

    /**
     * @param $storeCode
     * @return mixed
     */
    public function getWebhookByStoreCode($storeCode)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where('store_code = ?', $storeCode);

        return $this->getConnection()->query($select)->fetchObject();
    }

    /**
     * @param $field
     * @param $value
     * @param array $data
     * @return bool
     */
    public function updateWebhook($field, $value, $data = [])
    {
        $where[] = $this->getConnection()->quoteInto($field . ' = ?', $value);
        $this->getConnection()->update($this->getMainTable(), $data, $where);
        return true;
    }

    /**
     * @return mixed
     */
    public function getAuthorizedWebhooks()
    {
        $integration = $this->getIntegration();
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['webhook_count' => 'COUNT(id)']
        )
            ->where('integration_id = ?', $this->integration->getId())
            ->where('is_oath_authorized = ?', '1')
            ->where('is_deleted = ?', '0');

        return $this->getConnection()->query($select)->fetchObject();
    }

    /**
     * @return mixed
     */
    public function getEnabledWebhooks()
    {
        $integration = $this->getIntegration();
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            ['webhook_count' => 'COUNT(id)']
        )
            ->where('integration_id = ?', $this->integration->getId())
            ->where('is_deleted = ?', '0');

        return $this->getConnection()->query($select)->fetchObject();
    }

    /**
     * @param null $integrationID
     * @return mixed
     */
    public function getWebHookWithUninstallUrl($integrationID = null)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where('integration_id = ?', $integrationID)
            ->where('uninstall_url <> ?', '');

        return $this->getConnection()->query($select)->fetchObject();
    }
}
