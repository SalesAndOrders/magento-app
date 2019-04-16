<?php

namespace SalesAndOrders\FeedTool\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.8') < 0) {
            $setup->getConnection()->dropTable(
                $setup->getTable('perspective_products')
            );
            $setup->getConnection()->dropTable(
                $setup->getTable('perspective_attribute_mapper')
            );
        }

        $installer->endSetup();
    }
}