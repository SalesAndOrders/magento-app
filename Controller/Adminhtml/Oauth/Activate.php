<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Controller\Adminhtml\Oauth;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\View\Result\PageFactory;
use \SalesAndOrders\FeedTool\Model\Integration\Activation;
use \Magento\Framework\Controller\ResultFactory;

/**
 * Comment is required here
 */
class Activate extends Action
{

    /**
     * @var Activation
     */
    protected $activationModel;
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Activate constructor.
     *
     * @param Context    $context
     * @param Activation $activation
     */
    public function __construct(
        Context $context,
        Activation $activation
    ) {
        $this->activationModel = $activation;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function execute()
    {
        $data = $this->activationModel->runActivation();

        /**
         * @var \Magento\Framework\Controller\Result\Json $result
         */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(
            [
                'status' => $data,
                'response' => $data,
                'message' => !$data ? "Error creating integration, try again later" : "Successfully activated"
            ]
        );
        return $result;
    }
}
