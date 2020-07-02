<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model\Config\Source\Filter\Category;

class InEx implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(){
        $ret[] = [
            'value' => 'include',
            'label' => 'Use products related from selected categories'
            ];
        $ret[] = [
            'value' => 'exclude',
            'label' => 'Exclude only products related to selected categories'
        ];
        return $ret;
    }
}
