<?php

namespace SalesAndOrders\FeedTool\Model;

use SalesAndOrders\FeedTool\Api\RestCallbackInterface;
use SalesAndOrders\FeedTool\Model\CronScheduler;

class RestCallback implements RestCallbackInterface
{

    protected $cronScheduler;

    public function __construct(
        CronScheduler $cronScheduler
    )
    {
        $this->cronScheduler = $cronScheduler;
    }

    public function products()
    {
        $response = $this->cronScheduler->sendProducts();
        return $response;
    }
}