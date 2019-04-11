<?php

namespace SalesAndOrders\FeedTool\Controller\Page;

use \SalesAndOrders\FeedTool\Model\Mapper;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var Mapper
     */
    protected $mapper;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Mapper $mapper
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->mapper = $mapper;
        parent::__construct($context);
    }
    /**
     * View  page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $mapper = $this->mapper->getMapperToAttributes();
        $result = $this->resultJsonFactory->create();
        $data = ['message' => 'Hello world 2!'];

        return $result->setData($data);
    }
}