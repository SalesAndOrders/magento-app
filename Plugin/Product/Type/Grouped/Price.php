<?php

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Grouped;

/**
 * Comment is required here
 */
class Price
{
    public function aroundGetPrice($subject, $proceed, $product)
    {
        if ($product->getTypeId() == "grouped") {
            $price = 0;
            if (!$price || $price == 0) {
                $childProductCollection = $product->getTypeInstance()->getAssociatedProducts($product);
                if ($childProductCollection &&
                !empty($childProductCollection && !$product->getData('temp_view_price'))
                ) {
                    foreach ($childProductCollection as $childrenProduct) {
                        if ($price == 0 || $price > $childrenProduct->getPrice()) {
                            $price = $childrenProduct->getPrice();
                        }
                    }
                    $product->setData('temp_view_price', true);
                }
            }
        } else {
            $returnValue = $proceed($product);
            $price = $returnValue;
        }
        return $price;
    }
}
