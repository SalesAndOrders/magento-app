<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Model;

use SalesAndOrders\FeedTool\Api\RestCallbackInterface;
use SalesAndOrders\FeedTool\Model\CronScheduler;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Webapi\Rest\Request;

use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

/**
 * Comment is required here
 */
class RestCallback implements RestCallbackInterface
{

    /**
     * @var \SalesAndOrders\FeedTool\Model\CronScheduler
     */
    protected $cronScheduler;
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;
    /**
     * @var ServiceOutputProcessor
     */
    protected $serviceOutputProcessor;
    /**
     * @var Request
     */
    protected $_request;
    protected $_storeManager;
    /**
     * @var \SalesAndOrders\FeedTool\Model\ResourceModel\WebHook
     */
    protected $webHookModel;

    /**
     * RestCallback constructor.
     *
     * @param \SalesAndOrders\FeedTool\Model\CronScheduler         $cronScheduler
     * @param ProductRepository                                    $productRepository
     * @param SearchCriteriaInterface                              $searchCriteria
     * @param ServiceOutputProcessor                               $serviceOutputProcessor
     * @param Request                                              $request
     * @param \SalesAndOrders\FeedTool\Model\ResourceModel\WebHook $webHookModel
     * @param  \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CronScheduler $cronScheduler,
        ProductRepository $productRepository,
        SearchCriteriaInterface $searchCriteria,
        ServiceOutputProcessor $serviceOutputProcessor,
        Request $request,
        WebHook $webHookModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->cronScheduler = $cronScheduler;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->webHookModel = $webHookModel;
    }

    /**
     * @return bool|mixed|string
     */
    public function actions()
    {
        $response = $this->cronScheduler->sendStoresActions();
        return $response;
    }

    /**
     * @return array|bool|float|int|mixed|string
     */
    public function products()
    {
        $search = $this->searchCriteria->setData('page_size', 2);
        $list = $this->productRepository->getList($search);
        $output = $this->serviceOutputProcessor->process(
            $list,
            \Magento\Catalog\Api\ProductRepositoryInterface::class,
            'getList'
        );
        return $output;
    }

    /**
     * @return bool|mixed
     */
    public function webhooks()
    {
        $params = $this->_request->getRequestData();
        $this->webHookModel->addIntegrationWebHook($params, 1);
        return true;
    }
    /**
     * @return int|mixed
    */
    public function webhooks_remove_all(){
        $params = $this->_request->getRequestData();
        return $this->webHookModel->deleteWebHookAllIntegrations();
    }
    /**
     * @return int|mixed
     */
    public function webhooks_remove_store(){
        $params = $this->_request->getRequestData();
        $store_code =  $this->_storeManager->getStore()->getCode();
        return $this->webHookModel->deleteWebHookStoreIntegrations($store_code);
    }
}
