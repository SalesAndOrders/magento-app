<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Configurable;

use Magento\Framework\App\Action\Context;

/**
 * Avoid 0$ price value for a configuralbe product
 * Price takes as a minimal value of varian/option (ralated simple product)
 */
class PriceResolver
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

    protected $optionPrice = null;
    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductFactory $_productloader
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ) {
        $this->_request = $context->getRequest();
        $this->_productloader = $_productloader;
    }
    /**
     * @param \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver\Interceptor $subject
     * @param float $result
     * @param \Magento\Framework\Pricing\SaleableInterface $product
     *
     * @return float
     *
     */
    public function afterResolvePrice(
        \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver\Interceptor $subject,
        $result,
        \Magento\Framework\Pricing\SaleableInterface $product
    ) {
        $moduleName = $this->getRequest()->getModuleName();
        $controllerName = $this->getRequest()->getControllerName();
        $actionName = $this->getRequest()->getActionName();
        if ($actionName == 'view'
            && $moduleName == 'catalog'
            && $controllerName == 'product'
            && $product->getTypeId() == "configurable"
            && $product->getId() == $this->getRequest()->getParam('id')
            && !is_null($this->getRequest()->getParam('oid')) // phpcs:ignore
        ) {
            $childProductId = (int) $this->getRequest()->getParam('oid');           // oid - option id
            $childrenProducts = $product->getTypeInstance()->getUsedProducts($product);  // get linked product
            if (is_null($this->optionPrice)) { // phpcs:ignore
                foreach ($childrenProducts as $child) {
                    if ($childProductId == $child->getId()) {
                        $childProduct = $this->_productloader->create()->load($childProductId);
                        $this->optionPrice = $childProduct->getFinalPrice();
                    }
                }
            }
            $result = $this->optionPrice;
        }
        return $result;
    }

    public function getRequest()
    {
        return $this->_request;
    }
}
