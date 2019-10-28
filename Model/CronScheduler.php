<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Model;

use Magento\Store\Model\StoreManagerInterface;
use \SalesAndOrders\FeedTool\Model\ResourceModel\Product;
use \SalesAndOrders\FeedTool\Model\ProductFactory;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook\Collection as WebhookCollection;
use SalesAndOrders\FeedTool\Model\Transport;
use SalesAndOrders\FeedTool\Model\Logger;

/**
 * Comment is required here
 */
class CronScheduler
{

    const ACTION_ITEMS_PER_PAGE = 500;

    const GLOBAL_ALL_STORE_VIEWS_ADMIN_ID = 0;

    /**
     * @var Product
     */
    protected $productResource;

    /**
     * @var \SalesAndOrders\FeedTool\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var WebHook
     */
    protected $webHook;

    /**
     * @var WebhookCollection
     */
    protected $webHookCollection;

    /**
     * @var \SalesAndOrders\FeedTool\Model\Transport
     */
    protected $transport;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManger;

    /**
     * @var \SalesAndOrders\FeedTool\Model\Logger
     */
    protected $logger;

    /**
     * CronScheduler constructor.
     */
    public function __construct(
        Product $productResource,
        ProductFactory $productFactory,
        WebHook $webHook,
        WebhookCollection $webHookCollection,
        Transport $transport,
        StoreManagerInterface $storeManger,
        Logger $logger
    ) {
        $this->productResource = $productResource;
        $this->productFactory = $productFactory;
        $this->webHook = $webHook;
        $this->webHookCollection = $webHookCollection;
        $this->transport = $transport;
        $this->storeManger = $storeManger;
        $this->logger = $logger;
    }

    /**
     * @return array
     *
     * sendStoresActions send cURL pages per store
     */
    public function sendStoresActions()
    {
        $logger = $this->logger->create('log_prod_actions', 'products');
        $logger->info('Begint to log all actions');
        try {
            $globalStore = $this->storeManger->getStore(self::GLOBAL_ALL_STORE_VIEWS_ADMIN_ID);
        } catch (\Exception $e) {
            $globalStore = false;
        }

        $condition = null;
        if ($globalStore && !empty($globalStore->getData())) {
            $condition = ['eq' => $globalStore->getCode()];
        }

        $webhooksCollection = $this->webHookCollection->getItems();
        $pagesCount = [];
        $webhooksData = [];
        $logger->info('Get all webhooks collection');
        if (!$webhooksCollection) {
            $logger->info('WebhooksCollection is empty. ' . date('d.m.Y H:i:s'));
            return [];
        }
        $logger->info('Get all pages / webhook (store)');
        foreach ($webhooksCollection as $webHook) {
            $pageCount = $this->productFactory->create()
                ->getCollection()
                ->addFieldToFilter('store_code', ['eq' => $webHook->getStoreCode()])
                ->setPageSize(self::ACTION_ITEMS_PER_PAGE)
                ->getLastPageNumber();
            $pagesCount[$webHook->getStoreCode()] = $pageCount;
            $webhooksData[$webHook->getStoreCode()] = $webHook->getData();
        }
        $logger->info('Pages data: ');
        $this->logger->log($pagesCount);

        $allResult = [];
        $collections = null;
        foreach ($pagesCount as $storeCode => $count) {
            $logger->info('Begin generate collection for Store: ' . $storeCode);
            $currentPage = 1;
            $result = [];
            for ($i = $currentPage; $i <= (int)$count; $i ++) {
                $collection = $this->productFactory->create()->getCollection()
                    ->addFieldToFilter(
                        'store_code',
                        [
                            ['eq' => $storeCode],
                            $condition
                        ]
                    )
                    ->setPageSize(self::ACTION_ITEMS_PER_PAGE)
                    ->setCurPage($i)
                    ->setOrder('store_code', 'asc');

                foreach ($collection as $item) {

                    if ($item->getStoreCode() && $item->getAction()) {
                        $result['pages'][$i]['base_store_url'] = $item->getStoreBaseUrl();
                        $result['pages'][$i]['store_code'] = $item->getStoreCode();
                        $result['pages'][$i]['actions'][$item->getAction()][] = $item->getId();
                    }
                }
                $collections[] = $collection;
            }
            // send to store product url his pages
            if (isset($result['pages'])) {
                foreach ($result['pages'] as $page) {
                    if (isset($webhooksData[$storeCode])
                        && isset($webhooksData[$storeCode]['products_webhook_url'])
                        && $webhooksData[$storeCode]['products_webhook_url']
                    ) {
                        $logger->info('Try to send page to store: ' .
                            $storeCode .
                            ' on URL ' .
                            $webhooksData[$storeCode]['products_webhook_url']);
                        // send curl
                        $response = $this->transport->sendData(
                            $webhooksData[$storeCode]['products_webhook_url'],
                            $page,
                            false
                        );
                        if (!empty($response['err'])) {
                            $logger->info('Error sending web-hooks: ' . $response['err']);
                        }
                        if (!empty($response['response'])) {
                            $logger->info('Response from sending web-hooks: ' . $response['response']);
                        }
                    }
                }
            }

            if (!empty($result)) {
                $allResult[$storeCode] = $result;
            }
        }
        $this->productResource->removeProductsCollections($collections);
        $logger->info('End of sending product actions at ' . date('d.m.Y H:i:s'));
        return $allResult;
    }
}
