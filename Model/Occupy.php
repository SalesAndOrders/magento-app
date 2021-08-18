<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SalesAndOrders\FeedTool\Model;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Model\ConfigInterface as ModelConfigInterface;

/**
 * Contains methods to check if some logic allowed to be executed
 * Primarily for plugins.
 */
class Occupy
{

    protected $_config;
    protected $_apiConfig;
    protected $request;

    public function __construct(
        ModelConfigInterface $_config
        ,\Magento\Webapi\Model\Rest\Config $_apiConfig
        ,RestRequest $request
    ) {
        $this->_config = $_config;
        $this->_apiConfig = $_apiConfig;
        $this->request = $request;
    }

    /**
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function isSandORequest():bool{
        $routes = $this->_apiConfig->getRestRoutes($this->request);
        if(empty($routes)){
            return false;
        }
        $route = $routes[0];
        $vendorName = explode('\\',$route->getServiceClass() )[0];
        return  $vendorName == 'SalesAndOrders';
    }
}
