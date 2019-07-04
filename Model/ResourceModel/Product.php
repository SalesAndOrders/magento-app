<?php

namespace SalesAndOrders\FeedTool\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as productCollection;
use \Magento\Store\Model\StoreManagerInterface;

class Product extends AbstractDb {

    /**]
     * @var productCollection
     */
    protected $productCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storemanager;

    /**
     * Product constructor.
     * @param Context $context
     * @param productCollection $productCollection
     */
    public function __construct(
        Context $context,
        productCollection $productCollection,
        StoreManagerInterface $storemanager
    )
    {
        $this->productCollection = $productCollection;
        $this->storemanager = $storemanager;
        parent::__construct($context);
    }


    protected function _construct()
    {
        $this->_init('perspective_products', 'id');
    }

    /**
     * @param $productData
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function saveEditedProduct($productData, $action = 'create')
    {
        $store_data = $productData->getStore()->getData();
        $store_code = isset($store_data['code']) ? $store_data['code'] : null;
        $store_id = isset($store_data['store_id']) ? $store_data['store_id'] : null;
        $storeBaseUrl = $this->storemanager->getStore($store_id)->getBaseUrl();
        if (!$store_code){
            return false;
        }

        $product = $this->getProductByFields([
            'product_id' => $productData->getId(),
            'store_code' => $store_code
        ]);

        if ($product) {
            $where = ['id = ?' => $product->id];
            $this->getConnection()
                ->update($this->getMainTable(),
                    [
                        'store_base_url' => $storeBaseUrl,
                        'edited' => date('Y-m-d H:i:s'),
                        'action' => $action
                    ], $where);
        } else {
            $this->getConnection()
                ->insert($this->getMainTable(),
                    [
                        'product_id' => $productData->getId(),
                        'product_sku' => $productData->getSku(),
                        'store_code' => $store_code,
                        'store_base_url' => $storeBaseUrl,
                        'edited' => date('Y-m-d H:i:s'),
                        'action' => $action
                    ]);
        }

        return true;
    }

    /**
     * @param int $value
     * @param string $field
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getProductByField($value = 0, $field = 'product_id')
    {
        $result = null;
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where($field . ' = ?', $value);
        $result = $this->getConnection()->query($select)->fetchObject();
        return $result;
    }

    public function getProductByFields($fields = [])
    {
        $result = null;
        $select = $this->getConnection()->select()->from($this->getMainTable());

        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $field => $value) {
                $select->where($field . ' = ?', $value);
            }
        }

        $result = $this->getConnection()->query($select)->fetchObject();
        return $result;
    }

    /**
     * @param int $value
     * @param string $field
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByField($value = 0, $field = 'product_id')
    {
        $where = [$field . ' = ?' => $value];
        $this->getConnection()->delete($this->getMainTable(), $where);
        return true;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteAllSendedProducts($products = [])
    {
        if (!empty($products)) {
            foreach ($products as $product) {
                $this->getConnection()->delete($this->getMainTable(), ['product_id = ?' => $product['id']]);
            }
        }
        return true;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAllProducts()
    {
        $select = $this->getConnection()->select()->from($this->getMainTable());
        $allChangedProducts = $this->getConnection()->query($select)->fetchAll();

        $filter = [];
        $result = [];
        $actions = [];
        if (!empty($allChangedProducts)) {
            foreach ($allChangedProducts as $changedProduct) {
                $filter[] = ['eq' => $changedProduct['product_id']];
                $actions[$changedProduct['product_id']] = $changedProduct['action'];
            }
            if ($filter) {
                $data = $this->productCollection->create();
                $data->addAttributeToSelect('*');
                $data->addFieldToFilter('entity_id', $filter);
                $result = $this->dataGenetator($data, $allChangedProducts, $actions);
            }
        }
        return $result;
    }

    /**
     * @param $products
     * @param $changedProducts
     * @param $actions
     * @return array
     */
    public function dataGenetator($products, $changedProducts, $actions)
    {
        $result = [];
        if (!empty($changedProducts)) {
            foreach ($changedProducts as $changedProduct) {
                if ($changedProduct['action'] == 'delete') {
                    $result[] = ['id' => $changedProduct['product_id'], 'action' => $changedProduct['action']];
                }
            }
        }
        if ($products) {
            foreach ($products as $datum) {
                if ($actions[$datum->getId()] != 'delete') {
                    $result[] = [
                        'id' => $datum->getId(),
                        //'sku' => $datum->getSku(),
                        //'title' => $datum->getName(),
                        //'description' => $datum->getDescription(),
                        //'price' => $datum->getPrice(),
                        //'availability' =>$datum->getQuantityAndStockStatus(),
                        //'link' =>$datum->getProductUrl(),
                        //'image_link' => $this->getProductImage($datum),
                        'action' => $actions[$datum->getId()]
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductImage($product)
    {
        $productImageUrl = '';
        if ($product->getImage()) {
            $store = $this->storemanager->getStore();
            $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();
        }
        return $productImageUrl;
    }
}