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
}
