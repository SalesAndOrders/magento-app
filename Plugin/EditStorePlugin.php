<?php

namespace SalesAndOrders\FeedTool\Plugin;

use Magento\Backend\Controller\Adminhtml\System\Store\Save;
use Magento\Store\Model\StoreManagerInterface;
use SalesAndOrders\FeedTool\Model\Transport;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

/**
 * Comment is required here
 */
class EditStorePlugin
{

    protected $beforeStoreCode = null;

    protected $storeManager;

    protected $transport;

    protected $webhookResource;

    public function __construct(
        StoreManagerInterface $storeManager,
        Transport $transport,
        WebHook $webHookResource
    ) {
        $this->storeManager = $storeManager;
        $this->transport = $transport;
        $this->webhookResource = $webHookResource;
    }

    public function beforeExecute(Save $subject)
    {
        $storeID = isset($subject->getRequest()
            ->getPost()['store']['store_id'])
                ? $subject->getRequest()->getPost()['store']['store_id']
                : null
        ;

        if ($storeID) {
            $store = $this->storeManager->getStore($storeID);
            $this->beforeStoreCode = $store->getCode();
        }
    }

    public function afterExecute(Save $subject, $result)
    {
        $newStoreCode = isset($subject->getRequest()
                    ->getPost()['store']['code'])
                        ? $subject->getRequest()->getPost()['store']['code']
                        : null;
        $storeBaseUrl = $this->storeManager
                ->getStore($subject->getRequest()->getPost()['store']['store_id'])
                ->getBaseUrl();

        if ($this->beforeStoreCode && $newStoreCode && $this->beforeStoreCode != $newStoreCode) {
            $webhook = $this->webhookResource->getWebhookByStoreCode($this->beforeStoreCode);
            if ($webhook && $webhook->account_update_url) {
                $data = $this->getData($storeBaseUrl, $newStoreCode, $this->beforeStoreCode);
                $this->transport->sendData($webhook->account_update_url, $data);
                // change webhook store code
                $this->webhookResource->updateWebhook('id', $webhook->id, ['store_code' => $newStoreCode]);
            }
        }
        return $result;
    }

    private function getData($newBaseUrl, $newStoreCode, $storeCode)
    {
        return [
            'store_base_url' => $newBaseUrl,
            'store_code' => $newStoreCode,
            'old_store_code' => $storeCode
        ];
    }
}
