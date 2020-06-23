<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Configurable;

/**
 * Avoid 0$ price value for a configuralbe product
 * Price takes as a minimal value of varian/option (ralated simple product)
 */
class Price
{
    public function aroundGetPrice($subject, $proceed, $product)
    {
        if ($product->getTypeId() == "configurable") {
            $price = $product->getFinalPrice() ? $product->getFinalPrice() : 0;
            if (!$price || $price == 0) {
                $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
                if ($childrenProducts && !empty($childrenProducts)) {
                    foreach ($childrenProducts as $childrenProduct) {
                        if ($price == 0 || $price > $childrenProduct->getPrice()) {     // avoid 0$ price value
                            $price = $childrenProduct->getPrice();                      // get mimimal price value
                        }
                    }
                }
            }
        } else {
            $returnValue = $proceed($product);
            $price = $returnValue;
        }
        return $price;
    }
}
