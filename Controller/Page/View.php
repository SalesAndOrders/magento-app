<?php

namespace SalesAndOrders\FeedTool\Controller\Page;

use \Magento\Framework\App\Action\Action;
use SalesAndOrders\FeedTool\Model\Product;

/**
 * Comment is required here
 */
class View extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Product
     */
    protected $productModel;

    /**
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Product $productModel
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productModel = $productModel;
        parent::__construct($context);
    }
    /**
     * View  page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //$result = $this->resultJsonFactory->create();
        //$this->productModel->testCase($this->getRequest()->getPost());
        //$data = ['message' => 'Hello world 2!'];
//        exit('1');
        $result = $this->resultRawFactory->create();
 
        // Return Raw Text or HTML data
        // $result->setContents('Hello World');
        $result->setContents('1');
 
        return $result;
        //return $result->setData($data);
    }
}
