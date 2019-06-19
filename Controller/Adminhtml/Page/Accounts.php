<?php

namespace SalesAndOrders\FeedTool\Controller\Adminhtml\Page;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\View\Result\LayoutFactory;

class Accounts extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $resultLayoutFactory;
    /**
     * View constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('SalesAndOrders_FeedTool::integration_module_accounts');
        $resultPage->getConfig()->getTitle()->prepend((__('Sales And Orders Accounts')));
        return $resultPage;
    }
}