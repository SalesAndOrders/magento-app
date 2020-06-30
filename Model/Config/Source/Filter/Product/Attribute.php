<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model\Config\Source\Filter\Product;

class Attribute implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_data;    // source data
    protected $_objectManager = null;
    protected $_attributeRepository = null;
    protected $_attributeFactory = null;
    protected $_attributeOption = null;

    /**
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOption
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    */
    public function __construct(
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOption,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\ObjectManager\ObjectManager $objectManager
    )
    {
        $this->_attributeFactory = $attributeFactory;
        $this->_attributeOption = $attributeOption;
        $this->_attributeRepository = $attributeRepository;
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
        foreach ($this->getData() as $item){
            $result[] = [
                  'value' => $item['attribute_code']
                , 'label' => $item['label']
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
            $searchCriteria = $this->_objectManager->create('\Magento\Framework\Api\SearchCriteria');
            $attributeList = $this->_attributeRepository->getList(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE
                ,$searchCriteria
            )->getItems();

            $res[] = [
                "attribute_code" => ""
                , "label" => ""
            ];


            foreach ($attributeList as $attribute) {
                $res[] = [
                    "attribute_code" => $attribute->getAttributeCode(),
                    "id" => $attribute->getAttributeId(),
                    "label" => $attribute->getDefaultFrontendLabel()
                ];
            }

            $res[] = [
                "attribute_code" => "entity_id"
                , "label" => "Product Id"
            ];
            $res[] = [
                "attribute_code" => "qty"
                , "label" => "Quantity"
            ];
            $res[] = [
                "attribute_code" => "is_in_stock"
                , "label" => "Is in stock"
            ];
            $res[] = [
                "attribute_code" => "min_price [price attribute]"
                , "label" => "Minimal Price"
            ];

            $this->_data = $res; // gives associated array, table fields as key in array.;
        }
        return  $this->_data;
    }
    /**
     * @param int $attId
     * @return array
     */
    public function getAttributeOptions($attId)
    {
        $att = $this->_attributeFactory->create()->load($attId);
        if ($att->getSourceModel() != "") {
            try {
                return $att->getSource()->getAllOptions();
            } catch (\Exception $e) {
                return [];
            }
        } else {
            $coll = $this->_attributeOption->create();
            return $coll->setAttributeFilter($attId)->setStoreFilter($this->getStoreId())->getData();
        }
    }

    /**
     * @return array
     */
    public function getAttributesList()
    {
        $typeCode = \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE;
        $searchCriteria = $this->_objectManager->create('\Magento\Framework\Api\SearchCriteria');
        $attributeList = $this->_attributeRepository->getList($typeCode, $searchCriteria)->getItems();

        $tmp = [];
        foreach ($attributeList as $attribute) {
            $tmp[] = [
                "attribute_id" => $attribute->getAttributeId(),
                "attribute_code" => $attribute->getAttributeCode(),
                "frontend_label" => $attribute->getDefaultFrontendLabel()
            ];
        }

        $attributeList[] = ["attribute_code" => "entity_id", "frontend_label" => "Product Id"];
        $attributeList[] = ["attribute_code" => "qty", "frontend_label" => "Quantity"];
        $attributeList[] = ["attribute_code" => "is_in_stock", "frontend_label" => "Is in stock"];
        $attributeList[] = ["attribute_code" => "min_price [price attribute]", "frontend_label" => "Minimal Price"];

        usort($attributeList, ['\Wyomind\SimpleGoogleShopping\Block\Adminhtml\Feeds\Edit\Tab\Filters', 'cmp']);

        return $attributeList;
    }

    /**
     * @return string
     */
    public function getJsData()
    {
        $attributeCodes = [];
        $attributeList = $this->getAttributesList();
        foreach ($attributeList as $attribute) {
            if (preg_match("/^[a-zA-Z0-9_]+$/", $attribute['attribute_code'])) {
                if (isset($attribute['attribute_id'])) {
                    $attributeOptions = $this->getAttributeOptions($attribute['attribute_id']);

                    $options = [];
                    foreach ($attributeOptions as $attributeOption) {
                        if (!empty($attributeOption['value'])) {
                            $options[] = ["value" => (isset($attributeOption['option_id'])) ? $attributeOption['option_id'] : $attributeOption['value'], "label" => isset($attributeOption['label']) ? $attributeOption['label'] : $attributeOption['value']];
                        }
                    }
                    if ($attribute['attribute_code'] != 'location') {
                        $attributeCodes[$attribute['attribute_code']] = $options;
                    }
                }
            }
        }
        return json_encode($attributeCodes);
    }

}
