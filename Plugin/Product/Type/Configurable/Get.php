<?php

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Configurable;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

class Get
{
    protected $configurableProduct;

    public function __construct(
        Configurable $configurableProduct
    )
    {
        $this->configurableProduct = $configurableProduct;
    }

    public function afterGet($subject, $product, $sku)
    {
        $value = null;
        $parent = $this->configurableProduct->getParentIdsByChild($product->getId());
        if (!empty($parent)) {
            $value = 1;
        }
        $product->getExtensionAttributes()->setIsVariant($value);
        return $product;
    }
}