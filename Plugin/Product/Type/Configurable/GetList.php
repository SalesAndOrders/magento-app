<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Configurable;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

/**
 * Comment is required here
 */
class GetList
{
    protected $configurableProduct;

    public function __construct(
        Configurable $configurableProduct
    ) {
        $this->configurableProduct = $configurableProduct;
    }

    public function afterGetList($subject, $products, $searchCriteria)
    {
        $productsData = $products->getItems();
        if (!empty($productsData)) {
            foreach ($productsData as $id => $product) {
                $value = null;
                $parent = $this->configurableProduct->getParentIdsByChild($id);
                if (!empty($parent)) {
                    $value = 1;
                    $products->getItems()[$id]->getExtensionAttributes()->setIsVariant($value);
                }
            }
        }
        $searchResult = &$products;

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $domainUrl = $this->getDomainUrl();

        $productItems = $searchResult->getItems();
        foreach ($productItems as $key => $product){
            $extensionAttributes  = $product->getExtensionAttributes();
            $fullUrlKey = $this->getFullProductUrlKey($domainUrl,$product->getProductUrl() );
            $extensionAttributes->setFullUrlKey($fullUrlKey);

            if($product->getTypeId() == "configurable"){
                $simpleProductList = $extensionAttributes->getConfigurableProductLinks();
                //TODO: optimization is required
                $collection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
                $collection->addAttributeToSelect('*');
                $collection->addFieldToFilter('entity_id',$simpleProductList);

                $storeId  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')
                    ->getStore()
                    ->getStoreId()
                ;

                $collection->addStoreFilter( $storeId );
                $collection->addUrlRewrite();
                $collection->load();
                $this->addParentProductUrl($collection,$extensionAttributes,$fullUrlKey);

                $productArray = $collection->toArray();
                $extensionAttributes->setChildProduct( [$productArray] );
            }else{
                continue;
            }
        }

        return $searchResult;
    }

    /**
     * @param string $domainUrl
     * @param string $productUrl
     * @return string
     */
    protected function getFullProductUrlKey($domainUrl, $productUrl){
        $fullUrlKey = explode($domainUrl,$productUrl)[1];       // exclude from Domain URl
        return explode('index.php/',$fullUrlKey)[1];    // exclude index.php
    }

    /**
     * Return Domain name from Store base url
     * @return string
    */
    protected function getDomainUrl(){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager  = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $store         = $storeManager->getStore();

        $storeCode     = $store->getCode();
        $baseStoreURL  = $store->getBaseUrl();
        $urlStorePrefix= $storeCode.'/';
        $domainUrl     = explode($urlStorePrefix,$baseStoreURL)[0];     // exclude store view prefix
        $domainUrl     = explode('index.php',$domainUrl)[0];    // exclude index.php
        return $domainUrl;
    }

    /**
     * Build and set parent product ulr with selected variant attributes options and price
     * @param object $productCollection
     * @param object $extensionAttributes
     * @param string $parentProductFullUrlKey
     */
    protected function addParentProductUrl(object &$productCollection, object $extensionAttributes,$parentProductFullUrlKey){
        $childProductUrl=[];
        foreach ($productCollection as &$childProduct) {
            $hash = [];
            foreach ($extensionAttributes->getConfigurableProductOptions() as $attributeOption) {
                $attributeId = $attributeOption->getData('attribute_id');
                $attributeCode = $attributeOption->getData('product_attribute')->getData('attribute_code');
                $hash[] = $attributeId.'='.$childProduct->getData($attributeCode);
            }
            $hashStr = implode('&',$hash);
            $oid = $childProduct->getId();          // selected price product
            $fullProductURL = $parentProductFullUrlKey.'?oid='.$oid.'#'.$hashStr;
            $childProduct->setData('parent_url', $fullProductURL);
            $childProductUrl[$oid] = $fullProductURL;
        }
        $extensionAttributes->setConfigurableOptionUrl([$childProductUrl]);
    }
}
