<?php

namespace SalesAndOrders\FeedTool\Model;

use SalesAndOrders\FeedTool\Api\RestCallbackInterface;
use SalesAndOrders\FeedTool\Model\CronScheduler;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;

class RestCallback implements RestCallbackInterface
{

    protected $cronScheduler;

    protected $productRepository;

    protected $searchCriteria;

    protected $serviceOutputProcessor;

    public function __construct(
        CronScheduler $cronScheduler,
        ProductRepository $productRepository,
        SearchCriteriaInterface $searchCriteria,
        ServiceOutputProcessor $serviceOutputProcessor
    )
    {
        $this->cronScheduler = $cronScheduler;
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
    }

    public function actions()
    {
        $response = $this->cronScheduler->sendProducts();
        return $response;
    }

    public function products()
    {
        $search = $this->searchCriteria->setData('page_size', 2);
        $list = $this->productRepository->getList($search);
        $output = $this->serviceOutputProcessor->process($list, 'Magento\Catalog\Api\ProductRepositoryInterface', 'getList');
        return $output;
    }
}