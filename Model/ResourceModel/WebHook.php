<?php

namespace SalesAndOrders\FeedTool\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;

use \Magento\Integration\Model\IntegrationFactory;
use Magento\Store\Model\StoreManagerInterface;
use \SalesAndOrders\FeedTool\Model\Integration\Activation as IntegrationActivation;
use \SalesAndOrders\FeedTool\Model\Transport;

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


    /**
     * WebHook constructor.
     * @param Context $context
     * @param IntegrationFactory $integrationFactory
     */
    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        StoreManagerInterface $storeManager,
        Transport $transport
    )
    {
        $this->integrationFactory = $integrationFactory;
        $this->integration = $this->integrationFactory->create()->load(IntegrationActivation::INTEGRATION_NAME, 'name');
        $this->storeManager = $storeManager;
        $this->transport = $transport;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('perspective_webhooks', 'id');
    }

    /**
     * @param array $insertData
     * @param int $authorize_flag
     * @return bool
     */
    public function addIntegrationWebHook($insertData = [], $authorize_flag = 0)
    {

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
                $this->getConnection()->update($this->_mainTable, $insertData, ['integration_id = ?' => $this->integration->getId()]);
            }
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

    public function uninstall($store_code = null)
    {
        $webHook = $this->getWebHookData($this->integration->getId(), $store_code);
        $result = false;
        if ($webHook && $webHook->uninstall_url) {
            // send to uninstall url data
            $storeBaseUrl = $this->storeManager->getStore()->getBaseUrl();
            $data = [
                'store_base_url' => $storeBaseUrl,
                'store_code' => $store_code
            ];
            $response = $this->transport->sendData($webHook->uninstall_url, $data);
            $decodeResult = json_decode($response['response']);
            // if code == 200 we delete webhook by store code from DB
            if (isset($decodeResult->status) && $decodeResult->status == IntegrationActivation::END_POINT_SUCCESS_CODE) {
                // remove webhook from DB
                $this->deleteWebHookByStoreCode($this->integration->getId(), $store_code);
                $result = true;
            }
        }

        return $result;
    }

    public function getWebhookByStoreCode($storeCode)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where('store_code = ?', $storeCode);

        return $this->getConnection()->query($select)->fetchObject();
    }
}