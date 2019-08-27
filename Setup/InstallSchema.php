<?php


namespace SalesAndOrders\FeedTool\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install Schema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        /**
         * Table - perspective_products
         */
        $table_perspective_perspective_products = $setup->getConnection()
                            ->newTable($setup->getTable('perspective_products'));

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
            'store_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'store_code of product event'
        );

        $table_perspective_perspective_products->addColumn(
            'store_base_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'storestore_base_url_code of product event'
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
         * Table - perspective_webhooks
         */
        $table_perspective_webhook = $setup->getConnection()->newTable($setup->getTable('perspective_webhooks'));

        $table_perspective_webhook->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
            'Entity ID'
        );

        $table_perspective_webhook->addColumn(
            'integration_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'id of integration'
        );

        $table_perspective_webhook->addColumn(
            'consumer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'id of consumer'
        );

        $table_perspective_webhook->addColumn(
            'is_oath_authorized',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            'flag'
        );

        $table_perspective_webhook->addColumn(
            'verify_url_endpoint',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'url'
        );

        $table_perspective_webhook->addColumn(
            'store_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'code'
        );

        $table_perspective_webhook->addColumn(
            'is_deleted',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => true, 'default' => '0'],
            'if deleted - CRON not used'
        );

        $table_perspective_webhook->addColumn(
            'products_webhook_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'url'
        );

        $table_perspective_webhook->addColumn(
            'account_update_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'url'
        );

        $table_perspective_webhook->addColumn(
            'uninstall_url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'url'
        );

        $setup->getConnection()->createTable($table_perspective_webhook);
    }
}
