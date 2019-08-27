<?php

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
class Delete extends Action
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
     * Delete constructor.
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
     * @throws \Exception
     */
    public function execute()
    {
        $data = $this->activationModel->deleteIntegration();

        /**
 * @var \Magento\Framework\Controller\Result\Json $result
*/
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(
            [
            'status' => $data
            ]
        );
        return $result;
    }
}
