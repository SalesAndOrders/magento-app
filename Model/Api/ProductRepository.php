<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Model\Api;

use Magento\Catalog\Api\Data\FilterBuilder;
use Magento\Catalog\Api\Data\FilterGroupBuilder;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Catalog\Model\Api\SearchCriteria\ProductCollectionProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductSearchResults;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Store\Model\ScopeInterface;
use SalesAndOrders\FeedTool\Helper\Config;

class ProductRepository
{
    /**
     * @var int
     */
    private $cacheLimit = 0;
    /**
     * @var Product[]
     */
    protected $instancesById = [];
    /**
     * @var ReadExtensions
     */
    private $readExtensions;
    /**
     * @@var \Magento\Catalog\Model\ProductRepository
    */
    protected $productRepositoory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @@var ScopeConfigInterface $scopeConfig
    */
    protected $scopeConfig;

    /**
     * @var ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;
    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var Config
     */
    protected $configHelper;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepositoory
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param Config $configHelper
     * @param ReadExtensions $readExtensions
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param CollectionProcessorInterface $collectionProcessor [optional]
     * @param int $cacheLimit [optional]
     */
    public function __construct(
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductRepository  $productRepositoory,
        ScopeConfigInterface $scopeConfig,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        Config $configHelper,
        ReadExtensions $readExtensions = null,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        $cacheLimit = 1000
    ){
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionFactory = $collectionFactory;
        $this->productRepositoory = $productRepositoory;
        $this->scopeConfig = $scopeConfig;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->readExtensions = $readExtensions ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ReadExtensions::class);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->cacheLimit = (int)$cacheLimit;

        $this->filterBuilder         = $filterBuilder;
        $this->filterGroupBuilder    = $filterGroupBuilder;

        $this->configHelper = $configHelper;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $this->filterByConfiguration($searchCriteria);
        $searchResult = $this->_getList($searchCriteria);
        $searchResult = $this->reduceAttributes($searchResult);
        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    protected function _getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->extensionAttributesJoinProcessor->process($collection);

        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        if( (int)$this->configHelper->getFiltersFilterIsSalable() ){
            $collection->getSelect()
                ->where(
                '`extension_attribute_stock_item`.`is_in_stock` = 1'
                )
            ;
            //filter by an image. If image are present in the product
            $items = $this->isSaleProductsWithImages();
            $collection->addAttributeToFilter('entity_id',['in' => $items] );
        }
        $this->collectionProcessor->process($searchCriteria, $collection);

        $collection->load();

        $collection->addCategoryIds();
        $this->addExtensionAttributes($collection);

        $nonSalableIds = [];
        foreach ($collection->getItems() as $key => &$product) {
            if( !$product->isSalable() ){
                $nonSalableIds[] = $key;
                continue;
            }
            $this->cacheProduct(
                $this->getCacheKey(
                    [
                        false,
                        $product->getStoreId()
                    ]
                ),
                $product
            );
            // set full url
            //$this->_storeManager->getStore()->getUrl('product/33');
            $fullURL = $product->getProductUrl();
            $product->getExtensionAttributes()->setFullUrl($fullURL);
        }
        /* This will make an issue. If searchCriteria will contain pageSize
            the actual result count could be less than expected.
            In other case need to load collection twice which is worse
        */
        foreach ($nonSalableIds as $key => $value ){
            $collection->removeItemByKey($key);
        }

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());


        return $searchResult;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function getSalableProductList(SearchCriteriaInterface $searchCriteria){

    }
    /**
     * Get key for cache
     *
     * @param array $data
     * @return string
     */
    protected function getCacheKey($data)
    {
        $serializeData = [];
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $serializeData[$key] = $value->getId();
            } else {
                $serializeData[$key] = $value;
            }
        }
        $serializeData = $this->serializer->serialize($serializeData);
        return sha1($serializeData);
    }
    /**
     * Add extension attributes to loaded items.
     *
     * @param Collection $collection
     * @return Collection
     */
    private function addExtensionAttributes(Collection $collection) : Collection
    {
        foreach ($collection->getItems() as $item) {
            $this->readExtensions->execute($item);
        }
        return $collection;
    }
    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                ProductCollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }
    /**
     * Add product to internal cache and truncate cache if it has more than cacheLimit elements.
     *
     * @param string $cacheKey
     * @param ProductInterface $product
     * @return void
     */
    private function cacheProduct($cacheKey, ProductInterface $product)
    {
        $this->instancesById[$product->getId()][$cacheKey] = $product;
        $this->saveProductInLocalCache($product, $cacheKey);

        if ($this->cacheLimit && count($this->instances) > $this->cacheLimit) {
            $offset = round($this->cacheLimit / -2);
            $this->instancesById = array_slice($this->instancesById, $offset, null, true);
            $this->instances = array_slice($this->instances, $offset, null, true);
        }
    }
    /**
     * Saves product in the local cache by sku.
     *
     * @param Product $product
     * @param string $cacheKey
     * @return void
     */
    private function saveProductInLocalCache(Product $product, string $cacheKey): void
    {
        $preparedSku = $this->prepareSku($product->getSku());
        $this->instances[$preparedSku][$cacheKey] = $product;
    }
    /**
     * Converts SKU to lower case and trims.
     *
     * @param string $sku
     * @return string
     */
    private function prepareSku(string $sku): string
    {
        return mb_strtolower(trim($sku));
    }

    /**
     * Returns much lighter amount of data
     * @param ProductSearchResults $searchResult
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
     * @deprecated
     */
    protected function reduceAttributes(ProductSearchResults $searchResult){

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
            unset($itemData['type_id']);
            unset($itemData['has_options']);
            unset($itemData['required_options']);
            unset($itemData['created_at']);
            unset($itemData['extension_attribute_stock_item_notify_stock_qty']);
            unset($itemData['extension_attribute_stock_item_low_stock_date']);

//          $item->setData($itemData);
            $res[] = $itemData;
        }
        $searchCriteria = $searchResult->getSearchCriteria();
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria( $searchCriteria );
        $searchResult->setItems($items);
        $searchResult->setTotalCount( count($items) );
        return $searchResult;
    }

    /**
     * Filters repository by module settings
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     */
    protected  function filterByConfiguration(SearchCriteriaInterface $searchCriteria){
        $arrFilterGroup = [];
        foreach ($searchCriteria->getFilterGroups() as $group){
            $arrFilterGroup[] = $group;
        }
        $this->conf_filterByProductType($arrFilterGroup);
        $this->conf_filterByAttributeSet($arrFilterGroup);
        $this->conf_filterByProductExcludeId($arrFilterGroup);
        $this->conf_filterByCategory($arrFilterGroup);
        $this->conf_filterByIsPrice($arrFilterGroup);
        $this->conf_filterByIsSalable($arrFilterGroup);
        $this->conf_filterByAttribute($arrFilterGroup);

        $searchCriteria->setFilterGroups($arrFilterGroup);
        return $searchCriteria;
    }
    protected function conf_filterByIsSalable(array &$arrFilterGroup){
        $value = (int)$this->configHelper->getFiltersFilterIsSalable();
        if($value != 1 ){
            return null;
        }

        $filter1 =   $this->filterBuilder
            ->setField('visibility')
            ->setValue('2,4')
            ->setConditionType('in')
            ->create();

        $arrFilterGroup[] = $this->filterGroupBuilder
            ->addFilter( $filter1 )
            ->create();

        $filter2 =   $this->filterBuilder
            ->setField('status')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $arrFilterGroup[] = $this->filterGroupBuilder
            ->addFilter( $filter2 )
            ->create();
/*
        $filter3 =   $this->filterBuilder
            ->setField('is_in_stock')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $arrFilterGroup[] = $this->filterGroupBuilder
            ->addFilter( $filter3 )
            ->create();
*/
    }
    protected function isSaleProductsWithImages(){
        $mCollection = $this->collectionFactory->create();
        $mCollection->getSelect()
            ->join(
                array('images_value_entity' => $mCollection->getTable('catalog_product_entity_media_gallery_value')),
                'e.entity_id = images_value_entity.entity_id',
                array('value_id')
            )
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns('e.entity_id')
            ->join(
                array('images_value_data' => $mCollection->getTable('catalog_product_entity_media_gallery')),
                'images_value_entity.value_id = images_value_data.value_id',
                array('count( images_value_data.value ) AS images_count')
            )
            ->group('e.entity_id')
            ->having('`images_count` > 0')
        ;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection();
        $resultRows = $connection->fetchAll($mCollection->getSelect()); // gives associated array, table fields as key in array.
        $ids = [];
        foreach($resultRows as $row){
            $ids[] = $row['entity_id'];
        }
        return $ids;
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
        $condition = 'in';
        $include_exclude = $this->configHelper->getFiltersCategoryInExCludeProducts();
        if('exclude' == $include_exclude ){
            $condition = 'nin';
        }

        $filter =   $this->filterBuilder
            ->setField('category_id')
            ->setValue($value)
            ->setConditionType($condition)
            ->create();

        $filter_group = $this->filterGroupBuilder
            ->addFilter( $filter )
            ->create();

        $arrFilterGroup[] = $filter_group;
    }
    protected function conf_filterByIsPrice(array &$arrFilterGroup){
        $value = (int)$this->configHelper->getFiltersFilterIsPrice();
        if(empty($value)){
            return null;
        }
        if($value != 1){
            return ;
        }
        $filter =   $this->filterBuilder
            ->setField('price')
            ->setValue(0.1)
            ->setConditionType('gt')
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
