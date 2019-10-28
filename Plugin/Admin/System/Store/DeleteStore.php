<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Admin\System\Store;

use Magento\Backend\Controller\Adminhtml\System\Store\DeleteStorePost;
use Magento\Store\Model\StoreManagerInterface;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

/**
 * Comment is required here
 */
class DeleteStore
{

    protected $storeManager;

    protected $deletedStoreCode = null;

    protected $webHookModel;

    public function __construct(
        StoreManagerInterface $storeManager,
        WebHook $webHookModel
    ) {
        $this->storeManager = $storeManager;
        $this->webHookModel = $webHookModel;
    }

    public function beforeExecute(DeleteStorePost $subject)
    {
        $store = $this->storeManager->getStore($subject->getRequest()->getParam('item_id'));
        if ($store && $store->getCode()) {
            $this->deletedStoreCode = $store->getCode();
        }
    }

    public function afterExecute(DeleteStorePost $subject, $result)
    {
        if ($this->deletedStoreCode) {
            // can delete webhook from db
            $this->webHookModel->uninstall($this->deletedStoreCode);
        }
        return $result;
    }
}
