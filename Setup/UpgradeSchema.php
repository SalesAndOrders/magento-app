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

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.24') < 0) {

            /**
             * perspective_products updates
             */
            $installer->getConnection()->addColumn(
                $installer->getTable('perspective_products'),
                'store_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'store_code of product event'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('perspective_products'),
                'store_base_url',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'storestore_base_url_code of product event'
                ]
            );

            /**
             * perspective_webhooks updates
             */
            $installer->getConnection()->addColumn(
                $installer->getTable('perspective_webhooks'),
                'is_deleted',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 11,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'if deleted - CRON not used'
                ]
            );

        }

        $installer->endSetup();
    }
}