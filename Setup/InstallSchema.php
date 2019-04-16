<?php


namespace SalesAndOrders\FeedTool\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {

        $table_perspective_perspective_products = $setup->getConnection()->newTable($setup->getTable('perspective_products'));

        $table_perspective_perspective_products->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $table_perspective_perspective_products->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [],
            'id of edited product'
        );

        $table_perspective_perspective_products->addColumn(
            'product_sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],

            'sku of edited product'
        );

        $table_perspective_perspective_products->addColumn(
            'edited',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],

            'edit date and time'
        );

        $table_perspective_perspective_products->addColumn(
            'action',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],

            'action of product event'
        );

        $setup->getConnection()->createTable($table_perspective_perspective_products);

    }
}
