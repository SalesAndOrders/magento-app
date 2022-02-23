<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace SalesAndOrders\FeedTool\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Model\IntegrationFactory;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class ProcessIntegrationConfig implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var ConfigBasedIntegrationManager $integrationManager
     */
    private $integrationManager;

    /**
     * @var IntegrationFactory $integrationFactory
     */
    private $integrationFactory;

    /**
     * @var string $integrationName
     */
    protected $integrationName = 'sales_and_orders';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ConfigBasedIntegrationManager $integrationManager,
        IntegrationFactory $integrationFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->integrationManager = $integrationManager;
        $this->integrationFactory = $integrationFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->integrationManager->processIntegrationConfig([$this->integrationName]);
        $integration = $this->integrationFactory->create()->load($this->integrationName, 'name');
        if ($integration && $integration->getId()) {
            /**
             * this value adding sttings for update and delete integration from admin panel
             */
            $integration->setSetupType(2);
            $integration->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
