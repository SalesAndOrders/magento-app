<?php

namespace SalesAndOrders\FeedTool\Controller\Webhook;

use \Magento\Framework\App\Action\Action;
use \SalesAndOrders\FeedTool\Model\WebHookFactory;

class Delete extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected $webhookFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        WebHookFactory $webHookFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->webhookFactory = $webHookFactory;
        parent::__construct($context);
    }
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $params = $this->getRequest()->getParams();
        $webhookID = isset($params['webhook']) ? $params['webhook'] : null;

        /**
         * We check isset webhookID and create factory of model WebHook and change flag is_deleted to =1
         */
        if ($webhookID) {
            $webhook = $this->webhookFactory->create()->load($webhookID);
            if ($webhook && $webhook->getId()) {
                $webhook->setIsDeleted(1);
                $webhook->save();
                $text = __('Webhook with ID=%1 deleted', $webhookID);
            }else{
                $text = __('Webhook with ID=%1 s doesnt exist', $webhookID);
            }
        }else{
            $text = __('Webhook params is doesnt exist');
        }

        $data = ['message' => $text];
        return $result->setData($data);
    }
}