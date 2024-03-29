<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Configurable;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Model\ConfigInterface as ModelConfigInterface;

/**
 * Comment is required here
 */
class GetList
{
    protected $configurableProduct;
    protected $_config;
    protected $_apiConfig;
    protected $request;
    /* @var \SalesAndOrders\FeedTool\Model\Occupy $_occupy    */
    protected $_occupy;

    public function __construct(
        Configurable $configurableProduct,
        ModelConfigInterface $_config,
        \Magento\Webapi\Model\Rest\Config $_apiConfig,
        RestRequest $request,
        \SalesAndOrders\FeedTool\Model\Occupy $_occupy
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->_occupy = $_occupy;
        $this->_config = $_config;
        $this->_apiConfig = $_apiConfig;
        $this->request = $request;
    }

    public function afterGetList($subject, $products, $searchCriteria)
    {
        if (!$this->_occupy->isSandORequest()) {  //affects only from sando requests
            return $products;
        }
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
        foreach ($productItems as $key => $product) {
            $extensionAttributes  = $product->getExtensionAttributes();
            $fullUrl =  $product->getProductUrl();
            $extensionAttributes->setFullUrl($fullUrl);
            $fullUrlKey = $this->getFullProductUrlKey($domainUrl, $fullUrl);
            $extensionAttributes->setFullUrlKey($fullUrlKey);

            if ($product->getTypeId() == "configurable") {
                $simpleProductList = $extensionAttributes->getConfigurableProductLinks();
                //TODO: optimization is required
                $collection = $objectManager->get(CollectionFactory::class)->create();
                $collection->addAttributeToSelect('*');

                $collection->joinField(
                    'stock_status',
                    'cataloginventory_stock_status',
                    'stock_status',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                )->addFieldToFilter(
                    'stock_status',
                    ['eq' => \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK]
                );
                $collection->addFieldToFilter('entity_id', $simpleProductList);
                $storeId  = $objectManager->get(StoreManagerInterface::class)
                    ->getStore()
                    ->getStoreId();

                $collection->addStoreFilter($storeId);
                $collection->addUrlRewrite();
                $collection->addCategoryIds();

                $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
                $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
                $collection->load();

                $collectionStockData = $this->getStockData($collection->getAllIds());
                $this->addParentProductUrl($collection, $extensionAttributes, $fullUrlKey);

                $productArray = $collection->toArray();
                $extensionAttributes->setChildProduct([$productArray]);
            } else {
                continue;
            }
        }

        return $searchResult;
    }

    /**
     * Retrieves stock data according to to the product collection
     */
    protected function getStockData(array $productIdList):array
    {
        //$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        //$objectManager->get(CollectionFactory::class);

        /*Get in stock product collection*/
        //$collection = $this->productCollection->create()->addFieldToSelect('*')
        //    ->setFlag('has_stock_status_filter', false)
        //    ->joinField(
        //        'stock_item',
        //        'cataloginventory_stock_item',
        //        'is_in_stock',
        //        'product_id=entity_id',
        //        'is_in_stock=1'
        //    );
        //debug
        //echo "<pre>";
        //print_r($collection->getData());
        //exit();
        $res = [];
        return $res;
    }

    /**
     * @param string $domainUrl
     * @param string $productUrl
     * @return string
     */
    protected function getFullProductUrlKey($domainUrl, $productUrl)
    {
        $fullUrlKey = explode($domainUrl, $productUrl)[1];       // exclude from Domain URl
        if (strpos($fullUrlKey, 'index.php/') === false) {
            return $fullUrlKey;
        }
        return explode('index.php/', $fullUrlKey)[1];    // exclude index.php;
    }

    /**
     * Return Domain name from Store base url
     * @return string
     */
    protected function getDomainUrl()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager  = $objectManager->get(StoreManagerInterface::class);
        $store         = $storeManager->getStore();

        $storeCode     = $store->getCode();
        $baseStoreURL  = $store->getBaseUrl();
        $urlStorePrefix= $storeCode.'/';
        $domainUrl     = explode($urlStorePrefix, $baseStoreURL)[0];     // exclude store view prefix
        $domainUrl     = explode('index.php', $domainUrl)[0];    // exclude index.php
        return $domainUrl;
    }

    /**
     * Build and set parent product ulr with selected variant attributes options and price
     * @param object $productCollection
     * @param object $extensionAttributes
     * @param string $parentProductFullUrlKey
     */
    protected function addParentProductUrl(
        object &$productCollection,
        object $extensionAttributes,
        $parentProductFullUrlKey
    ) {
        $childProductUrl=[];
        foreach ($productCollection as &$childProduct) {
            $hash = [];
            foreach ($extensionAttributes->getConfigurableProductOptions() as $attributeOption) {
                $attributeId = $attributeOption->getData('attribute_id');
                $attributeCode = $attributeOption->getData('product_attribute')->getData('attribute_code');
                $hash[] = $attributeId.'='.$childProduct->getData($attributeCode);
            }
            $hashStr = implode('&', $hash);
            $oid = $childProduct->getId();          // selected price product
            $fullProductURL = $parentProductFullUrlKey.'?oid='.$oid.'#'.$hashStr;
            $childProduct->setData('parent_url', $fullProductURL);
            $childProductUrl[$oid] = $fullProductURL;
        }
        $extensionAttributes->setConfigurableOptionUrl([$childProductUrl]);
    }
}
