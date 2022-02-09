<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Grouped;

/**
 * Comment is required here
 */
class Price
{
    /* @var \SalesAndOrders\FeedTool\Model\Occupy $_occupy    */
    protected $_occupy;
    public function __construct(
        \SalesAndOrders\FeedTool\Model\Occupy $_occupy
    ) {
        $this->_occupy = $_occupy;
    }
    public function aroundGetPrice($subject, $proceed, $product)
    {
        if ($product->getTypeId() == "grouped"
            && $this->_occupy->isSandORequest()     //executes only if called by API
        ) {
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
