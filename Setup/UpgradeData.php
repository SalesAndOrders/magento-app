<?php

namespace SalesAndOrders\FeedTool\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.7    ') < 0) {
            $data = [
                ['key' => 'id', 'name' => 'ID', 'required' => 1],
                ['key' => 'title', 'name' => 'title', 'required' => 1],
                ['key' => 'description', 'name' => 'description', 'required' => 1],
                ['key' => 'image_link', 'name' => 'image_link', 'required' => 1],
                ['key' => 'condition', 'name' => 'condition', 'required' => 1],
                ['key' => 'availability', 'name' => 'availability', 'required' => 1],
                ['key' => 'price', 'name' => 'price', 'required' => 1],
                ['key' => 'sale_price', 'name' => 'sale_price', 'required' => 1],
                ['key' => 'gtin', 'name' => 'gtin', 'required' => 1],
                ['key' => 'brand', 'name' => 'brand', 'required' => 1],
                ['key' => 'link', 'name' => 'link', 'required' => 1],
                ['key' => 'sku', 'name' => 'sku', 'required' => 1],

                ['key' => 'cost_price', 'name' => 'cost_price', 'required' => 0],
                ['key' => 'mpn', 'name' => 'mpn', 'required' => 0],
                ['key' => 'color', 'name' => 'color', 'required' => 0],
                ['key' => 'material', 'name' => 'material', 'required' => 0],
                ['key' => 'pattern', 'name' => 'pattern', 'required' => 0],
                ['key' => 'size', 'name' => 'size', 'required' => 0],
                ['key' => 'custom_label_0', 'name' => 'custom_label_0', 'required' => 0],
                ['key' => 'custom_label_1', 'name' => 'custom_label_1', 'required' => 0],
                ['key' => 'custom_label_2', 'name' => 'custom_label_2', 'required' => 0],
                ['key' => 'custom_label_3', 'name' => 'custom_label_3', 'required' => 0],
                ['key' => 'custom_label_4', 'name' => 'custom_label_4', 'required' => 0],
                ['key' => 'shipping', 'name' => 'shipping', 'required' => 0],
                ['key' => 'shipping_weight', 'name' => 'shipping_weight', 'required' => 0],
                ['key' => 'shipping_width', 'name' => 'shipping_width', 'required' => 0],
                ['key' => 'shipping_height', 'name' => 'shipping_height', 'required' => 0],
                ['key' => 'shipping_length', 'name' => 'shipping_length', 'required' => 0],
                ['key' => 'gender', 'name' => 'gender', 'required' => 0],
                ['key' => 'age_group', 'name' => 'age_group', 'required' => 0],
                ['key' => 'product_type', 'name' => 'product_type', 'required' => 0],
                ['key' => 'item_group_id', 'name' => 'item_group_id', 'required' => 0],
                ['key' => 'meta_title', 'name' => 'meta_title', 'required' => 0],
            ];
            foreach ($data as $bind) {
                $setup->getConnection()
                    ->insertForce($setup->getTable('perspective_attribute_mapper'), $bind);
            }
        }
    }
}