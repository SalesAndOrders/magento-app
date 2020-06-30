<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model\Config\Source;

class Mode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Default')],
            ['value' => 1, 'label' => __('Develop')],
            ['value' => 2, 'label' => __('Staging')],
        ];
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
