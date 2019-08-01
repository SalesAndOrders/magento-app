<?php

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Configurable;


class Price
{
    public function aroundGetPrice($subject, $proceed, $product)
    {
        if ($product->getTypeId() == "configurable"){
            $price = $product->getFinalPrice() ? $product->getFinalPrice() : 0;
            if (!$price || $price == 0) {
                $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);
                if ($childrenProducts && !empty($childrenProducts)) {
                    foreach ($childrenProducts as $childrenProduct) {
                        if ($price == 0 || $price > $childrenProduct->getPrice()) {
                            $price = $childrenProduct->getPrice();
                        }
                    }
                }
            }
        }
        else {
            $returnValue = $proceed($product);
            $price = $returnValue;
        }
        return $price;
    }
}