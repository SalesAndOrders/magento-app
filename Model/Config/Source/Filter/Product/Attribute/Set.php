<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model\Config\Source\Filter\Product\Attribute;

class Set implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_data;    // source data
    protected $_attributeSetRepository = null;
    protected $_objectManager = null;

    /**
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
    */
    public function __construct(
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository

    )
    {
        $this->_attributeSetRepository = $attributeSetRepository;
        $this->_objectManager = $objectManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->prepareData();
    }

    /** Convert to correct format
     * @return array
    */
    protected function prepareData(){
        $result = [];
        foreach ($this->getData() as $productSet){
            $result[] = [
                  'value' => $productSet->getData('attribute_set_id')
                , 'label' => $productSet->getData('attribute_set_name')
            ];
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getData()
    {
        if( is_null($this->_data) ){
            $typeCode = \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE;
            $filterGroup = $this->_objectManager->create('\Magento\Framework\Api\Search\FilterGroup');
            $filter = $this->_objectManager->create('\Magento\Framework\Api\Filter');
            $filter->setField('entity_type_code');
            $filter->setConditionType('eq');
            $filter->setValue($typeCode);
            $filterGroup->setFilters([$filter]);

            $searchCriteria = $this->_objectManager->create('\Magento\Framework\Api\SearchCriteria');
            $searchCriteria->setFilterGroups([$filterGroup]);

            $this->_data = $this->_attributeSetRepository->getList($searchCriteria)->getItems();
        }
        return  $this->_data;
    }



    public function getLabelByValue($value){
        $list = $this->toOptionArray();
        foreach ($list as $el){
            $label =  $el['value'] == $value ? $el['label'] : null;
            if($label){
                return (string)$label;
            }
        }
    }
}
