<?php

namespace SalesAndOrders\FeedTool\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use SalesAndOrders\FeedTool\Model\ResourceModel\Product;

/**
 * Event for action after product save
 */
class CatalogProductSaveAfter implements ObserverInterface
{

    /**
     * @var Product
     */
    protected $productResourceModel;

    /**
     * CatalogProductSaveAfter constructor.
     *
     * @param Product $productResourceModel
     */
    public function __construct(
        Product $productResourceModel
    ) {
        $this->productResourceModel = $productResourceModel;
    }

    /**
     * @param  Observer $observer
     * @return bool|void
     */
    public function execute(Observer $observer)
    {
        // TODO: Implement execute() method.
        $product = $observer->getProduct();
        if (!$product->getProductAction()) {
            return false;
        }
        $action = $product->getProductAction();
        $result = $this->productResourceModel->saveEditedProduct($product, $action);
        return true;
    }
}
