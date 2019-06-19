<?php

namespace SalesAndOrders\FeedTool\Plugin;

use Magento\Backend\Controller\Adminhtml\System\Store\Save;
use Magento\Store\Model\StoreManagerInterface;

class EditStorePlugin {

    protected $beforeStoreCode = null;

    protected $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
    }

    public function beforeExecute(Save $subject)
    {
        $storeID = isset($subject->getRequest()->getPost()['store']['store_id']) ? $subject->getRequest()->getPost()['store']['store_id'] : null;

        if ($storeID) {
            $store = $this->storeManager->getStore($storeID);
            $this->beforeStoreCode = $store->getCode();
        }
    }

    public function afterExecute(Save $subject, $result)
    {
        $newStoreCode = isset($subject->getRequest()->getPost()['store']['store_code']) ? $subject->getRequest()->getPost()['store']['store_code'] : null;
        if ($this->beforeStoreCode && $newStoreCode && $this->beforeStoreCode != $newStoreCode) {

        }
        return $result;
    }
}