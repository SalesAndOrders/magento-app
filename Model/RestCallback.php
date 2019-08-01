<?php

namespace SalesAndOrders\FeedTool\Model;

use SalesAndOrders\FeedTool\Api\RestCallbackInterface;
use SalesAndOrders\FeedTool\Model\CronScheduler;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Webapi\Rest\Request;

use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

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
    /**
     * @var \SalesAndOrders\FeedTool\Model\ResourceModel\WebHook
     */
    protected $webHookModel;

    /**
     * RestCallback constructor.
     * @param \SalesAndOrders\FeedTool\Model\CronScheduler $cronScheduler
     * @param ProductRepository $productRepository
     * @param SearchCriteriaInterface $searchCriteria
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param Request $request
     * @param \SalesAndOrders\FeedTool\Model\ResourceModel\WebHook $webHookModel
     */
    public function __construct(
        CronScheduler $cronScheduler,
        ProductRepository $productRepository,
        SearchCriteriaInterface $searchCriteria,
        ServiceOutputProcessor $serviceOutputProcessor,
        Request $request,
        WebHook $webHookModel
    )
    {
        $this->cronScheduler = $cronScheduler;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
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
        $output = $this->serviceOutputProcessor->process($list, 'Magento\Catalog\Api\ProductRepositoryInterface', 'getList');
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
}