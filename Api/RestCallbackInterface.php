<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

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
