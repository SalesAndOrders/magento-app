<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model\System\Message;

use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class CustomNotification
 */
class NewVersionSystemMessage implements MessageInterface
{
    /**
     * Message identity for sando magento app
     */
    const MESSAGE_IDENTITY = 'new_version_sando_feedTool_system_message';
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var  \Magento\Framework\HTTP\Client\Curl $curl
     */
    protected $curl;
    /**
     * @var \SalesAndOrders\FeedTool\Helper\Config $config
     */
    protected $config;
    /**
     * Notifier Pool
     *
     * @var NotifierPool
     */
    protected $notifierPool;

    /***
     *
     * @param NotifierPool $notifierPool
     * @param \SalesAndOrders\FeedTool\Helper\Config $config
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(
        NotifierPool $notifierPool,
        \SalesAndOrders\FeedTool\Helper\Config $config,
        \Magento\Framework\HTTP\Client\Curl $curl,
        ObjectManagerInterface $objectManager
    ) {
        $this->notifierPool = $notifierPool;
        $this->config = $config;
        $this->curl = $curl;
        $this->objectManager = $objectManager;
    }

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
        if ($this->isNewerVersion()) {
            /** @var string $sampleUrl */
            //Sample system config url will be used for "Read Details" link in notification message
            // phpcs:ignore Generic.Files.LineLength.TooLong
            $readDetailsUrl = "https://marketplace.magento.com/sales-and-orders-magento-app.html#product.info.details.release_notes";

            // Add notice
            $this->notifierPool->addNotice(
                'New version of Sales and Orders deliver important updates',
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'The latest release of Sales and Orders is now generally available. Includes code enhancements along with new functionality and quality improvements.',
                $readDetailsUrl
            );
        }
        return $this->isNewerVersion();
    }

    /*
     * Retrieve system message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.TooLong
            '<a target="_blank" href="https://www.salesandorders.com/" ><img alt="&nbsp;&nbsp; Sales and Orders logo" style="height: 25px; position: relative; top: 5px; margin-right: 20px; margin-left: 10px;" src="https://www.salesandorders.com/images/logo.png"></a> New version of <a target="_blank" href="https://marketplace.magento.com/sales-and-orders-magento-app.html">Sales and Orders available</a> deliver important updates. <a target="_blank" href="https://marketplace.magento.com/sales-and-orders-magento-app.html#product.info.details.release_notes">More details...</a>'
        );
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

    /**
     * @return bool
     */
    protected function isNewerVersion():bool
    {
        //get current module version
        $currentVersion = $this->objectManager
                                    ->get(ResourceInterface::class)
                                    ->getDbVersion('SalesAndOrders_FeedTool')
        ;
        $this->curl->get($this->config->getUpdateURL());  // get method
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Content-Length", 200);
        $result = json_decode($this->curl->getBody(), true);      // output of curl request

        //for now don't notify user of a new version, maybe in the future ;)
        //print_r($result['packages']['sales_and_orders/magento-app'][0]['version']);die;
        //$receivedVersion = $result['packages']['sales_and_orders/magento-app'][0]['version'] ?? 0;
        //return $currentVersion < $receivedVersion;
        return false;
    }
}
