<?php

namespace SalesAndOrders\FeedTool\Api;

/**
 * Comment is required here
 */
interface RestCallbackInterface
{
    /**
     * @return mixed
     */
    public function actions();
    /**
     * @return mixed
     */
    public function products();
    /**
     * @return mixed
     */
    public function webhooks();
}
