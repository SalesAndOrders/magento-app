<?php

namespace SalesAndOrders\FeedTool\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Integration\Model\ConfigBasedIntegrationManager;
use \Magento\Integration\Model\IntegrationFactory;

/**
 * Upgrade Data script
 */
class UpgradeData implements UpgradeDataInterface
{

    private $integrationManager;

    private $integrationFactory;

    protected $integrationName = 'sales_and_orders';

    public function __construct(
        ConfigBasedIntegrationManager $integrationManager,
        IntegrationFactory $integrationFactory
)
    {
        $this->integrationManager = $integrationManager;
        $this->integrationFactory = $integrationFactory;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->integrationManager->processIntegrationConfig([$this->integrationName]);
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