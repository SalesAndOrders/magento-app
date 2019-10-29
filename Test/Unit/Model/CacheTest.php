<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Test\Unit\Model;

use \PHPUnit\Framework\TestCase;
use \SalesAndOrders\FeedTool\Model\Cache;

/**
 * Comment is required here
 */
class CacheTest extends TestCase
{

    protected $typeListInterface;

    protected $pool;

    protected $object = null;

    public function setUp()
    {
        $this->typeListInterface = $this->createMock(\Magento\Framework\App\Cache\TypeListInterface::class);
        $this->pool = $this->createMock(\Magento\Framework\App\Cache\Frontend\Pool::class);

        $this->object = new Cache(
            $this->typeListInterface,
            $this->pool
        );
    }

    /**
     * @param $types
     *
     * @dataProvider dataProvider
     */
    public function testClearCache($types)
    {
        $this->typeListInterface->expects($this->any())->method('cleanType')->withAnyParameters();
        $cacheFront = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);
        $cacheFront->expects($this->any())->method('getBackend')->with()->willReturn($cacheFront);
        $cacheFront->expects($this->any())->method('clean')->with()->willReturn(true);
        $result = $this->object->cleanCahes($types);
        $this->assertSame(true, $result);
    }

    public function dataProvider()
    {
        return [
            [['block_html', 'template']],
            [['full_page']]
        ];
    }
}
