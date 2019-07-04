<?php

namespace SalesAndOrders\FeedTool\Model;

use \SalesAndOrders\FeedTool\Model\ResourceModel\Product;
use \SalesAndOrders\FeedTool\Model\ProductFactory;

class CronScheduler
{

    protected $url = 'http://test182.perspective.net.ua/api/test/close/';

    /**
     * @var Product
     */
    protected $productResource;

    protected $productFactory;

    /**
     * CronScheduler constructor.
     */
    public function __construct(
        Product $productResource,
        ProductFactory $productFactory
    )
    {
        $this->productResource = $productResource;
        $this->productFactory = $productFactory;
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
     */
    public function sendActions()
    {
        $currentPage = 1;
        $pageCount = $this->productFactory->create()
            ->getCollection()
            ->setPageSize(500)
            ->getLastPageNumber();

        $result = [];
        for ($i = $currentPage; $i <= (int)$pageCount; $i ++) {
            $collection = $this->productFactory->create()->getCollection()
                ->setPageSize(500);
            $collection->setCurPage($i);
            $result['pages'][$i]['base_store_url'] = 'test';
            $result['pages'][$i]['store_id'] = 'test';
            foreach ($collection->load() as $item) {
                $result['pages'][$i]['actions'][] = $item->getId();
            }
            // send $i page to product URL
        }
        return $result;
    }
}