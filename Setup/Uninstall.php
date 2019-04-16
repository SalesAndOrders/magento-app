<?php
namespace SalesAndOrders\FeedTool\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->getConnection()->dropTable(
            $setup->getTable('perspective_products')
        );
        $setup->getConnection()->dropTable(
            $setup->getTable('perspective_attribute_mapper')
        );

        $setup->endSetup();
    }
}
