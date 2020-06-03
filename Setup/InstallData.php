<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Integration\Model\ConfigBasedIntegrationManager;
use \Magento\Integration\Model\IntegrationFactory;
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    private $integrationManager;

    private $integrationFactory;

    protected $integrationName = 'sales_and_orders';

    public function __construct(
        ConfigBasedIntegrationManager $integrationManager,
        IntegrationFactory $integrationFactory
    ) {
        $this->integrationManager = $integrationManager;
        $this->integrationFactory = $integrationFactory;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // integration is being created by \Magento\Integration\Setup\Recurring::install
        //        $this->integrationManager->processIntegrationConfig([$this->integrationName]);
        $integration = $this->integrationFactory->create()->load($this->integrationName, 'name');
        if ($integration && $integration->getId()) {
            /**
             * this value adding sttings for update and delete integration grom admin panel
             */
            $integration->setSetupType(2);
            $integration->save();
        }
    }
}
