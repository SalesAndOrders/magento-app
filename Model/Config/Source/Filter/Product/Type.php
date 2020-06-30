<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model\Config\Source\Filter\Product;

class Type implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_data;    // source data
    protected $_objectManager = null;

    /**
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
    */
    public function __construct(
        \Magento\Framework\ObjectManager\ObjectManager $objectManager
    )
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
            return $this->prepareData();
//        return [
//            ['value' => 0, 'label' => __('Default')],
//            ['value' => 1, 'label' => __('Develop')],
//            ['value' => 2, 'label' => __('Staging')],
//        ];
    }

    /** Convert to correct format
     * @return array
    */
    protected function prepareData(){
        $result = [];
        foreach ($this->getData() as $item){
            $result[] = [
                  'value' => $item['type_id']
                , 'label' => $item['type_id']
            ];
        }
        return $result;
    }

    /**
     * Retrieves existed/used product types
     * @return string
     */
    public function getData()
    {
        if( is_null($this->_data) ){
			$resource = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('catalog_product_entity'); //gives table name with prefix

            $select = $connection->select();    /* @var \Magento\Framework\Db\Select $select */
            $select->from(['p' => $tableName], ['type_id'])
                   ->group(['type_id'])
            ;
            $this->_data = $connection->fetchAll($select); // gives associated array, table fields as key in array.;
        }
        return  $this->_data;
    }
}
