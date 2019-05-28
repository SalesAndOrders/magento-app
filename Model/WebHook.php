<?php

namespace SalesAndOrders\FeedTool\Model;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;

use \Magento\Integration\Model\IntegrationFactory;
use \SalesAndOrders\FeedTool\Model\Integration\Activation as IntegrationActivation;

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

    /**
     * WebHook constructor.
     * @param Context $context
     * @param IntegrationFactory $integrationFactory
     */
    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory
    )
    {
        $this->integrationFactory = $integrationFactory;
        $this->integration = $this->integrationFactory->create()->load(IntegrationActivation::INTEGRATION_NAME, 'name');

        parent::__construct($context);

        if ($this->integration && $this->integration->getId()) {
            $this->integrationWebhook = $this->getWebHookData($this->integration->getId());
        }
    }

    public function _construct()
    {
        // TODO: Implement _construct() method.
    }

    /**
     * @param array $insertData
     * @param int $authorize_flag
     * @return bool
     */
    public function addIntegrationWebHook($insertData = [], $authorize_flag = 0)
    {
        if ($this->integration && $this->integration->getId()) {
            $insertData += [
                'integration_id' => $this->integration->getId(),
                'consumer_id' => $this->integration->getConsumerId(),
                'is_oath_authorized' => $authorize_flag
            ];
            if (!$this->integrationWebhook) {
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
    public function getWebHookData($integrationId = 0)
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
        if ($this->integrationWebhook) {
            $this->getConnection()->delete($this->_mainTable, ['integration_id = ?' => $integrationId]);
        }
        return true;
    }
}