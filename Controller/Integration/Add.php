<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Controller\Integration;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\View\Result\PageFactory;
use \SalesAndOrders\FeedTool\Model\Integration\Activation;
use \Magento\Framework\Controller\ResultFactory;

use Magento\Integration\Model\ConfigBasedIntegrationManager;

/**
 * Comment is required here
 */
class Add extends Action
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
     * @var ConfigBasedIntegrationManager
     */
    protected $integrationManager;

    /**
     * Add constructor.
     *
     * @param Context                       $context
     * @param Activation                    $activation
     * @param ConfigBasedIntegrationManager $integrationManager
     */
    public function __construct(
        Context $context,
        Activation $activation,
        ConfigBasedIntegrationManager $integrationManager
    ) {
        $this->activationModel = $activation;
        $this->resultFactory = $context->getResultFactory();
        $this->integrationManager = $integrationManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->integrationManager->processIntegrationConfig(['sales_and_orders']);

        /**
 * @var \Magento\Framework\Controller\Result\Json $result
*/
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(
            [
            'status' => true,
            'response' => 'Integration successfully added'
            ]
        );
        return $result;
    }
}
