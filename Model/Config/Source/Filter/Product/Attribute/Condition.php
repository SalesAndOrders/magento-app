<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model\Config\Source\Filter\Product\Attribute;

class Condition implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_data;    // source data
    protected $_objectManager = null;

    /**
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
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
        return [
            ["value" => "", "label" => ""],
            ["value" => "eq", "label" => "="],
            ["value" => "gt", "label" => ">"],
            ["value" => "lt", "label" => "<"],
            ["value" => "gteq", "label" => "&ge;"],
            ["value" => "lteq", "label" => "&le;"],
            ["value" => "neq", "label" => "&ne;"],
            ["value" => "like", "label" => "like"],
            ["value" => "nlike", "label" => "not like"],
            ["value" => "null", "label" => "is null"],
            ["value" => "notnull", "label" => "is not null"],
            ["value" => "in", "label" => "in"],
            ["value" => "nin", "label" => "not in"],
        ]
            ;
    }
}
