<?php

namespace SalesAndOrders\FeedTool\Api;

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
}