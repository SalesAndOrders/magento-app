<?php

namespace SalesAndOrders\FeedTool\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;

use \Magento\Integration\Model\IntegrationFactory;
use Magento\Store\Model\StoreManagerInterface;
use \SalesAndOrders\FeedTool\Model\Integration\Activation as IntegrationActivation;
use \SalesAndOrders\FeedTool\Model\Transport;
use SalesAndOrders\FeedTool\Model\Cache;

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
     * @param Context $context
     * @param IntegrationFactory $integrationFactory
     */
    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        StoreManagerInterface $storeManager,
        Transport $transport,
        Cache $cacheModel
    )
    {
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

    public function getIntegration()
    {
        if (!$this->integration || !$this->integration->getId()){
            $this->integration = $this->integrationFactory->create()->load(IntegrationActivation::INTEGRATION_NAME, 'name');
        }
        $integration = $this->integration;
        return $integration;
    }
    /**
     * @param array $insertData
     * @param int $authorize_flag
     * @return bool
     */
    public function addIntegrationWebHook($insertData = [], $authorize_flag = 0)
    {
        $this->integration = $this->getIntegration();
        if ($this->integration && $this->integration->getId()) {
            $insertData['store_code'] = isset($insertData['store_code']) ? $insertData['store_code'] : $this->storeManager->getStore()->getCode();
            $integrationWebHook = $this->getWebHookData($this->integration->getId(), $insertData['store_code']);
            $insertData += [
                'integration_id' => $this->integration->getId(),
                'consumer_id' => $this->integration->getConsumerId(),
                'is_oath_authorized' => $authorize_flag
            ];
            if (!$integrationWebHook) {
                $this->getConnection()->insert($this->_mainTable, $insertData);
            }else{
                $store_code = $insertData['store_code'];
                unset($insertData['store_code']);
                $where[] = $this->getConnection()->quoteInto('integration_id = ?', $this->integration->getId());
                $where[] = $this->getConnection()->quoteInto('store_code = ?', $store_code);
                $this->getConnection()->update($this->_mainTable, $insertData, $where);
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
     * @return mixed
     * @throws \Zend_Db_Statement_Exception
     */
    public function getWebHookData($integrationId = 0, $store_code = '')
    {
        $select = $this->getConnection()->select()
            ->from($this->_mainTable)
            ->where('integration_id = ?', $integrationId)
            ->where('store_code = ?', $store_code);

        return $this->getConnection()->query($select)->fetchObject();
    }

    public function getCustomWebHookData($integrationId = 0)
    {
        $select = $this->getConnection()->select()
            ->from($this->_mainTable)
            ->where('integration_id = ?', $integrationId);

        return $this->getConnection()->query($select)->fetchObject();
    }

    /**
     * @param int $integrationId
     */
    public function deleteWebHook($integrationId = 0)
    {
        $this->getConnection()->delete($this->_mainTable, ['integration_id = ?' => $integrationId]);
        return true;
    }

    public function deleteWebHookByStoreCode($integrationId = 0, $store_code = '')
    {
        $where[] = $this->getConnection()->quoteInto('integration_id = ?', $integrationId);
        $where[] = $this->getConnection()->quoteInto('store_code = ?', $store_code);
        $this->getConnection()->delete($this->_mainTable, $where);
        return true;
    }

    public function deleteWebHookByIntegration($integrationId = 0)
    {
        $where[] = $this->getConnection()->quoteInto('integration_id = ?', $integrationId);
        $this->getConnection()->delete($this->_mainTable, $where);
        return true;
    }

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

    public function getWebhookByStoreCode($storeCode)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where('store_code = ?', $storeCode);

        return $this->getConnection()->query($select)->fetchObject();
    }

    public function updateWebhook($field, $value, $data = [])
    {
        $where[] = $this->getConnection()->quoteInto($field . ' = ?', $value);
        $this->getConnection()->update($this->getMainTable(), $data, $where);
        return true;
    }

    public function getAuthorizedWebhooks()
    {
        $integration = $this->getIntegration();
        $select = $this->getConnection()->select()->from('perspective_webhooks',
            array('webhook_count' => 'COUNT(id)'))
            ->where('integration_id = ?', $this->integration->getId())
            ->where('is_oath_authorized = ?', '1')
            ->where('is_deleted = ?', '0');

        return $this->getConnection()->query($select)->fetchObject();
    }

    public function getEnabledWebhooks()
    {
        $integration = $this->getIntegration();
        $select = $this->getConnection()->select()->from('perspective_webhooks',
            array('webhook_count' => 'COUNT(id)'))
            ->where('integration_id = ?', $this->integration->getId())
            ->where('is_deleted = ?', '0');

        return $this->getConnection()->query($select)->fetchObject();
    }

    public function getWebHookWithUninstallUrl($integrationID = null)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where('integration_id = ?', $integrationID)
            ->where('uninstall_url <> ?', '');

        return $this->getConnection()->query($select)->fetchObject();
    }
}