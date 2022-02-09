<?php


namespace SalesAndOrders\FeedTool\Model\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

class ProductRepository extends \Magento\Catalog\Model\ProductRepository
{
    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria) // phpcs:ignore
    {
        return parent::getList($searchCriteria);
    }
}
