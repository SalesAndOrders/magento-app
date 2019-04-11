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

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.7') < 0) {

            /**
             * Mapper table
             */
            $table_perspective_mapper = $setup->getConnection()->newTable($setup->getTable('perspective_attribute_mapper'));

            $table_perspective_mapper->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
                'Entity ID'
            );

            $table_perspective_mapper->addColumn(
                'key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                200,
                [],
                'key of one mapper element'
            );

            $table_perspective_mapper->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],

                'name of one mapper elemen'
            );

            $table_perspective_mapper->addColumn(
                'required',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [],

                'required of this mapper element'
            );

            $setup->getConnection()->createTable($table_perspective_mapper);
        }

        $installer->endSetup();
    }
}