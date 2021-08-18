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
    /* @var \SalesAndOrders\FeedTool\Model\Occupy $_occupy    */
    protected $_occupy;
    public function __construct(
        \SalesAndOrders\FeedTool\Model\Occupy $_occupy
    ) {
        $this->_occupy = $_occupy;
    }
    public function aroundGetPrice($subject, $proceed, $product)
    {
        if (
                $product->getTypeId() == "configurable"
                && $this->_occupy->isSandORequest()     //executes only if called by API
        ) {
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
