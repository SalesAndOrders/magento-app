<?php

namespace SalesAndOrders\FeedTool\Plugin\Admin\System\Integration;

use Magento\Integration\Controller\Adminhtml\Integration\Delete as DeleteIntegration;
use Magento\Store\Model\StoreManagerInterface;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

/**
 * Comment is required here
 */
class Delete
{

    protected $storeManager;

    protected $integrationID = null;

    protected $webHookModel;

    public function __construct(
        StoreManagerInterface $storeManager,
        WebHook $webHookModel
    ) {
        $this->storeManager = $storeManager;
        $this->webHookModel = $webHookModel;
    }

    public function beforeExecute(DeleteIntegration $subject)
    {
        $this->integrationID = $subject->getRequest()->getParam('id');
    }

    public function afterExecute(DeleteIntegration $subject, $result)
    {
        if ($this->integrationID) {
            // can delete webhook from db and send unninstall URL
            $this->webHookModel->uninstallAll($this->integrationID);
        }
        return $result;
    }
}
