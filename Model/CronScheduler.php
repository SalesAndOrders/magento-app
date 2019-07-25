<?php

namespace SalesAndOrders\FeedTool\Model;

use Magento\Store\Model\StoreManagerInterface;
use \SalesAndOrders\FeedTool\Model\ResourceModel\Product;
use \SalesAndOrders\FeedTool\Model\ProductFactory;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook\Collection as WebhookCollection;
use SalesAndOrders\FeedTool\Model\Transport;
use SalesAndOrders\FeedTool\Model\Logger;

class CronScheduler
{

    const ACTION_ITEMS_PER_PAGE = 500;

    const GLOBAL_ALL_STORE_VIEWS_ADMIN_ID = 0;

    protected $url = 'http://test182.perspective.net.ua/api/test/close/';

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
    )
    {
        $this->productResource = $productResource;
        $this->productFactory = $productFactory;
        $this->webHook = $webHook;
        $this->webHookCollection = $webHookCollection;
        $this->transport = $transport;
        $this->storeManger = $storeManger;
        $this->logger = $logger;
    }

    /**
     * Send all edited products by cron
     */
    public function sendProducts()
    {
        $productsData = $this->productResource->getAllProducts();
        $response = $this->send($productsData);
        return $response;
    }

    /**
     * @param $productsData
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function send($productsData)
    {
        $result = [];
        $data['test_status'] = 'products';
        $data['products'] = $productsData;
        $dataJson = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST,           true );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $dataJson);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $this->isSuccess($productsData, $response, $info);
        if ($response) {
            $result = (array)json_decode($response);
        }
        return $result;
    }

    /**
     * @param bool $response
     * @param array $info
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isSuccess($products = [], $response = false, $info = [])
    {
        if (!$response) {
            return false;
        }

        if (!empty($info) && isset($info['http_code'])) {
            if ($info['http_code'] == 200) {
                $this->productResource->deleteAllSendedProducts($products);
                return true;
            }
        }
        return false;
    }

    /**
     * @return array
     *
     * sendStoresActions send cURL pages per store
     *
     */
    public function sendStoresActions()
    {
        $logger = $this->logger->create('log_prod_actions', 'products');
        $logger->info('Begint to log all actions');
        try{
            $globalStore = $this->storeManger->getStore(self::GLOBAL_ALL_STORE_VIEWS_ADMIN_ID);
        }catch (\Exception $e){
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
        if ($webhooksCollection) {
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
            if (!empty($pagesCount)) {
                foreach ($pagesCount as $storeCode => $count) {
                    $logger->info('Begin generate collection for Store: ' . $storeCode);
                    $currentPage = 1;
                    $result = [];
                    for ($i = $currentPage; $i <= (int)$count; $i ++) {
                        $collection = $this->productFactory->create()->getCollection()
                            ->addFieldToFilter('store_code',
                                [
                                    ['eq' => $storeCode],
                                    $condition
                                ]
                                )
                            ->setPageSize(self::ACTION_ITEMS_PER_PAGE)
                            ->setCurPage($i)
                            ->setOrder('store_code','asc');

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
                            if (isset($webhooksData[$storeCode]) &&
                                isset($webhooksData[$storeCode]['products_webhook_url']) &&
                                $webhooksData[$storeCode]['products_webhook_url']) {
                                $logger->info('Try to send page to store: ' . $storeCode . ' on URL ' . $webhooksData[$storeCode]['products_webhook_url']);
                                // send curl
                                $this->transport->sendData($webhooksData[$storeCode]['products_webhook_url'], $page);
                            }
                        }
                    }

                    if (!empty($result)) {
                        $allResult[$storeCode] = $result;
                    }
                }
            }

        }
        $this->productResource->removeProductsCollections($collections);
        $logger->info('End of sending product actions at ' . date('d.m.Y H:i:s'));
        return $allResult;
    }
}