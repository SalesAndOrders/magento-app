<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Controller\Webhook;

use \Magento\Framework\App\Action\Action;
use \SalesAndOrders\FeedTool\Model\WebHookFactory;
use \SalesAndOrders\FeedTool\Model\Logger;

/**
 * Comment is required here
 */
class Delete extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected $webhookFactory;

    protected $logger;

    /**
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        WebHookFactory $webHookFactory,
        Logger $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->webhookFactory = $webHookFactory;
        $this->logger = $logger;
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
         * We check isset webhookID and create factory of model WebHook and change flag is_deleted to = 1
         */
        $logger = $this->logger->create('delete_webhook_id_' . $webhookID, 'webhooks');
        if ($webhookID) {
            $webhook = $this->webhookFactory->create()->load($webhookID);
            $logger->info('Load webhook with ID = ' . $webhookID);
            if ($webhook && $webhook->getId()) {
                $webhook->setIsDeleted(1);
                $webhook->save();
                $text = __('Webhook with ID=%1 deleted', $webhookID);
                $logger->info('Change deteted floag is_deleted to 1');
            } else {
                $text = __('Webhook with ID=%1 s doesnt exist', $webhookID);
                $logger->info('Webhook with ID=' . $webhookID . ' is doesnt exist');
            }
        } else {
            $text = __('Webhook params is doesnt exist');
            $logger->info('Webhook params is doesnt exist');
        }

        $data = ['message' => $text];
        return $result->setData($data);
    }
}
