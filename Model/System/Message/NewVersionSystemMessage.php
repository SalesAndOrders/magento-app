<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model\System\Message;

use Magento\Framework\Notification\MessageInterface;

/**
 * Class CustomNotification
 */
class NewVersionSystemMessage implements MessageInterface
{
    /**
     * Message identity
     */
    const MESSAGE_IDENTITY = 'new_version_sando_feedTool_system_message';

    /**
     * Retrieve unique system message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return self::MESSAGE_IDENTITY;
    }

    /**
     * Check whether the system message should be shown
     *
     * @return bool
     */
    public function isDisplayed()
    {
        // The message will be shown
        //todo check new version availability
        $isNewerVersionAvailable = false;
//        if($isNewerVersionAvailable){
            $isNewerVersionAvailable = !$isNewerVersionAvailable;
//        }
        return $isNewerVersionAvailable;
    }

    /**
     * Retrieve system message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __('New Version of module <a href="https://marketplace.magento.com/sales-and-orders-magento-app.html" ><img alt="logo" src="https://marketplace.magento.com/media/catalog/product/cache/cc46e20e0a519cf92e024f4762fc8af3/c/1/c186_240x240-glyph-logo.png"> Sales and Orders available </a>. Please update');
    }

    /**
     * Retrieve system message severity
     * Possible default system message types:
     * - MessageInterface::SEVERITY_CRITICAL
     * - MessageInterface::SEVERITY_MAJOR
     * - MessageInterface::SEVERITY_MINOR
     * - MessageInterface::SEVERITY_NOTICE
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_NOTICE;
    }
}
