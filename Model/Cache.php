<?php

namespace SalesAndOrders\FeedTool\Model;

use \Magento\Framework\App\Cache\TypeListInterface;
use \Magento\Framework\App\Cache\Frontend\Pool;

class Cache
{
    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * Cache constructor.
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    )
    {
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * types[] = 'config','block_html' ... etc
     * @param null $types
     * @return bool
     */
    public function cleanCahes($types = null)
    {
        if (!$types) {
            return false;
        }

        if (!is_array($types) && is_string($types)) {
            $types[] = $types;
        }

        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        return true;
    }
}