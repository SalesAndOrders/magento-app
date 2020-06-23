<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Model\Api;

use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Store\Model\ScopeInterface;

class ProductRepository //extends \Magento\Catalog\Model\ProductRepository
{
    /**
     * @@var \Magento\Catalog\Model\ProductRepository
    */
    protected $productRepositoory;

    /**
     * @@var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
    */
    public function __construct(
         \Magento\Catalog\Model\ProductRepository  $productRepositoory
        ,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        ,\Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
    ){
        $this->productRepositoory = $productRepositoory;
        $this->scopeConfig = $scopeConfig;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {

                        // Get Product List by Search criteria
        return $searchResult = $this->productRepositoory->getList($searchCriteria);


        $res = [];
        $seoUrlSuffix = $this->scopeConfig->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            1
        );

        // get SEO  URL suffix
        $items = $searchResult->getItems();   /** @var Magento\Catalog\Model\ProductSearchResults $items */
        foreach ($items as &$item) {
            $itemData = $item->getData();

            unset($itemData['attribute_set_id']);
            unset($itemData['typeet_id']);
            unset($itemData['has_options']);
            unset($itemData['required_options']);
            unset($itemData['created_at']);
            unset($itemData['extension_attribute_stock_item_notify_stock_qty']);
            unset($itemData['extension_attribute_stock_item_low_stock_date']);

//            $item->setData($itemData);
            $res[] = $itemData;
        }
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setItems($items);
        return $searchResult;

    }
}
