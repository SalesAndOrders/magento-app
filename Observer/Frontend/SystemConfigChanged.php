<?php

namespace SalesAndOrders\FeedTool\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;


class SystemConfigChanged implements ObserverInterface
{

    /**
     * @var Product
     */
    protected $productResourceModel;

    protected $storeManger;

    /**
     * CatalogProductSaveAfter constructor.
     * @param Product $productResourceModel
     */
    public function __construct(
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManger = $storeManager;
    }

    /**
     * if we added product, we dont have id product entity
     * if we have id in product entity - this is edit product event
     * @param Observer $observer
     * @return bool|void
     */
    public function execute(Observer $observer)
    {

        return true;
    }
}
