<?php

namespace SalesAndOrders\FeedTool\Controller\Adminhtml\Oauth;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\View\Result\PageFactory;
use \SalesAndOrders\FeedTool\Model\Integration\Activation;
use \Magento\Framework\Controller\ResultFactory;

class Activate extends Action
{

    protected $activationModel;

    protected $resultFactory;

    public function __construct(
        Context $context,
        Activation $activation
    )
    {
        $this->activationModel = $activation;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->activationModel->activateIntegration();

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData([
            'status' => $data
        ]);
        return $result;
    }
}