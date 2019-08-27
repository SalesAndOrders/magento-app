<?php

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Bundle;

/**
 * Comment is required here
 */
class Price
{
    public function aroundGetPrice($subject, $proceed, $product)
    {
        if ($product->getTypeId() == "bundle") {
            $price = 0;
            if (!$price || $price == 0) {
                $childrenProducts = $product->getTypeInstance(true)
                    ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);

                if ($childrenProducts && !empty($childrenProducts)) {
                    foreach ($childrenProducts as $childrenProduct) {
                        if ($price == 0 || $price > $childrenProduct->getPrice()) {
                            $price = $childrenProduct->getPrice();
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
