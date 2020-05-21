<?php


namespace SalesAndOrders\FeedTool\Model\Api;


class ProductRepository extends \Magento\Catalog\Model\ProductRepository
{
    /**
     * @inheritdoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return parent::getList($searchCriteria);
    }
}
