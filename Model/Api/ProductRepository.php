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
     * @var \Magento\Catalog\Api\Data\FilterBuilder
     */
    protected $filterBuilder;
    /**
     * @var \Magento\Catalog\Api\Data\FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var \SalesAndOrders\FeedTool\Helper\Config
     */
    protected $configHelper;
    /**
     * * @param \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
    */
    public function __construct(
         \Magento\Catalog\Model\ProductRepository  $productRepositoory
        ,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        ,\Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory
        ,\Magento\Framework\Api\FilterBuilder $filterBuilder
        ,\Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
        ,\SalesAndOrders\FeedTool\Helper\Config $configHelper
    ){
        $this->productRepositoory = $productRepositoory;
        $this->scopeConfig = $scopeConfig;
        $this->searchResultsFactory = $searchResultsFactory;

        $this->filterBuilder         = $filterBuilder;
        $this->filterGroupBuilder    = $filterGroupBuilder;

        $this->configHelper = $configHelper;
    }

    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $this->filterByConfiguration($searchCriteria);  //filter by cofig values by selected scope store
        // Get Product List by Search criteria
        // runs original method
        $searchResult = $this->productRepositoory->getList($searchCriteria);
        $searchResult = $this->reduceAttributes($searchResult);
        return $searchResult;
    }

    /**
     * Returns much lighter amount of data
     * @param Magento\Catalog\Model\ProductSearchResults $searchResult
     * @return Magento\Catalog\Model\ProductSearchResults
     */
    protected function reduceAttributes(\Magento\Catalog\Model\ProductSearchResults $searchResult){

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
    /**
     * Filters repository by module settings
    */
    protected  function filterByConfiguration(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria){
        $arrFilterGroup = [];
        foreach ($searchCriteria->getFilterGroups() as $group){
            $arrFilterGroup[] = $group;
        }
        $this->conf_filterByProductType($arrFilterGroup);
        $this->conf_filterByProductExcludeId($arrFilterGroup);
        $this->conf_filterByCategory($arrFilterGroup);
        $this->conf_filterByAttribute($arrFilterGroup);

        $searchCriteria->setFilterGroups($arrFilterGroup);
        return $searchCriteria;
    }
    protected function conf_filterByProductType(array &$arrFilterGroup){
        $value = $this->configHelper->getFiltersFilterProductType();
        if(empty($value)){
            return null;
        }
        $filter =   $this->filterBuilder
            ->setField('type_id')
            ->setValue($value)
            ->setConditionType('in')
            ->create();

        $filter_group = $this->filterGroupBuilder
            ->addFilter( $filter )
            ->create();

        $arrFilterGroup[] = $filter_group;
    }
    protected function conf_filterByAttributeSet(array &$arrFilterGroup){
        $value = $this->configHelper->getFiltersFilterAttributeSet();
        if(empty($value)){
            return null;
        }
        $filter =   $this->filterBuilder
            ->setField('attribute_set_id')
            ->setValue($value)
            ->setConditionType('in')
            ->create();

        $filter_group = $this->filterGroupBuilder
            ->addFilter( $filter )
            ->create();

        $arrFilterGroup[] = $filter_group;
    }
    protected function conf_filterByProductExcludeId(array &$arrFilterGroup){
        $value = $this->configHelper->getFiltersFilterProductExcludeId();
        if(empty($value)){
            return null;
        }
        $filter =   $this->filterBuilder
            ->setField('entity_id')
            ->setValue($value)
            ->setConditionType('nin')
            ->create();

        $filter_group = $this->filterGroupBuilder
            ->addFilter( $filter )
            ->create();

        $arrFilterGroup[] = $filter_group;
    }
    protected function conf_filterByCategory(array &$arrFilterGroup){
        $value = $this->configHelper->getFiltersCategoryCatId();
        if(empty($value)){
            return null;
        }
        $filter =   $this->filterBuilder
            ->setField('category_id')
            ->setValue($value)
            ->setConditionType('in')
            ->create();

        $filter_group = $this->filterGroupBuilder
            ->addFilter( $filter )
            ->create();

        $arrFilterGroup[] = $filter_group;
    }
    protected function conf_filterByAttribute(array &$arrFilterGroup){
        $value = $this->configHelper->getFiltersAttribute();
        if(empty($value)){
            return null;
        }
        foreach ($value as $attribute){
            $filter =   $this->filterBuilder
                ->setField($attribute['field'])
                ->setValue($attribute['value'])
                ->setConditionType($attribute['condition'])
                ->create();

            $filter_group = $this->filterGroupBuilder
                ->addFilter( $filter )
                ->create();

            $arrFilterGroup[] = $filter_group;
        }
    }

}
