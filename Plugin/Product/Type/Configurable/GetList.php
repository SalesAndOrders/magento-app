<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Product\Type\Configurable;

use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;
/**
 * Comment is required here
 */
class GetList
{
    protected $configurableProduct;
    /**
     * @@var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * @@var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;

    public function __construct(
        Configurable $configurableProduct
        ,\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        ,\Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->configurableProduct = $configurableProduct;
    }

    /**
     *
     * @param \Magento\Catalog\Model\ProductRepository\Interceptor $subject
     * @param \Magento\Catalog\Model\ProductSearchResults $products
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Catalog\Model\ProductSearchResults
     */
    public function afterGetList($subject, $products, $searchCriteria):\Magento\Catalog\Model\ProductSearchResults
    {

        $storeId = $this->storeManager->getStore()->getId(); // get store Id
        $seoUrlSuffix = $this->getSeoUrlSufix($storeId);     //get SEO Url Suffix

        $productsData = $products->getItems();
        if (!empty($productsData)) {
            foreach ($productsData as $id => $product) {
                                                // set SeoURLSuffix
                $this->updateUrlKeyWithSeoSuffix($product,$seoUrlSuffix);
                                                // add configurable variants
                $value = null;
                $parent = $this->configurableProduct->getParentIdsByChild($id);
                if (!empty($parent)) {
                    $value = 1;
                    $products->getItems()[$id]->getExtensionAttributes()->setIsVariant($value);
                }
            }
        }
        return $products;
    }

    /**
     * @param int $storeId
     * @return string
    */
    protected function getSeoUrlSufix($storeId){
        return $this->scopeConfig->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * Add $seoUrlSuffix tp url_key
     * @param Magento\Catalog\Model\Product\Interceptor $productItem
     * @param string $seoUrlSuffix
    */
    protected function updateUrlKeyWithSeoSuffix($productItem,$seoUrlSuffix){
        $urlKey = $productItem->getData('url_key');
        $urlKey = $urlKey.$seoUrlSuffix;
        $productItem->setData('url_key',$urlKey);
    }
}
