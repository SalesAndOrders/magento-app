<?php

namespace SalesAndOrders\FeedTool\Controller\Adminhtml\Page\Accounts;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\View\Result\LayoutFactory;
use\Magento\Framework\Controller\ResultFactory;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

class Delete extends Action
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

    protected $webHookModel;

    protected $resultFactory;
    /**
     * View constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        WebHook $webHookModel,
        ResultFactory $resultFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->webHookModel = $webHookModel;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $this->webHookModel->uninstall($this->getRequest()->getParam('store_code'));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}