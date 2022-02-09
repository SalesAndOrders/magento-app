<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2021 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Controller\Adminhtml\Notification;

use Magento\Backend\App\AbstractAction as BackendAction;
use Magento\Backend\App\Action\Context as BackendActionContext;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;

/**
 * Class Index
 */
class Version extends BackendAction
{
    /**
     * Notifier Pool
     *
     * @var NotifierPool
     */
    protected $notifierPool;

    /***
     * Index action constructor
     *
     * @param BackendActionContext $context
     * @param NotifierPool $notifierPool
     */
    public function __construct(
        BackendActionContext $context,
        NotifierPool $notifierPool
    ) {
        parent::__construct($context);
        $this->notifierPool = $notifierPool;
    }

    /**
     * Create Sample Notification Messages
     *
     * @return ResultRedirect
     */
    public function execute()
    {
        /**
         * Sample system config url will be used for "Read Details" link in notification message
         *
         * @var string $sampleUrl
         */
        //$sampleUrl = $this->getUrl('adminhtml/integration_module/notification/version');
        $sampleUrl = "https://marketplace.magento.com/sales-and-orders-magento-app.html";

        // Add notice
        $this->notifierPool->addNotice(
            'Notice Title',
            'Notice description text.',
            // Add "Read Details" link
            $sampleUrl
        );

        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('adminhtml/');

        return $resultRedirect;
    }
}
