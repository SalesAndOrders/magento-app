<?php

namespace SalesAndOrders\FeedTool\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use SalesAndOrders\FeedTool\Model\ResourceModel\Product;

/**
 * event on ProductSaveBefore
 */
class CatalogProductSaveBefore implements ObserverInterface
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
     * if we added product, we dont have id product entity
     * if we have id in product entity - this is edit product event
     *
     * @param  Observer $observer
     * @return bool|void
     */
    public function execute(Observer $observer)
    {
        // TODO: Implement execute() method.
        $product = $observer->getProduct();
        $action = 'create';
        if ($product->getId() && $product->getId()!==null) {
            $action = 'update';
        }
        $product->setData('product_action', $action);
        return true;
    }
}
