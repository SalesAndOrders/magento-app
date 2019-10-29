<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Configurable;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

/**
 * Comment is required here
 */
class GetList
{
    protected $configurableProduct;

    public function __construct(
        Configurable $configurableProduct
    ) {
        $this->configurableProduct = $configurableProduct;
    }

    public function afterGetList($subject, $products, $searchCriteria)
    {
        $productsData = $products->getItems();
        if (!empty($productsData)) {
            foreach ($productsData as $id => $product) {
                $value = null;
                $parent = $this->configurableProduct->getParentIdsByChild($id);
                if (!empty($parent)) {
                    $value = 1;
                    $products->getItems()[$id]->getExtensionAttributes()->setIsVariant($value);
                }
            }
        }
        return $products;
    }
}
