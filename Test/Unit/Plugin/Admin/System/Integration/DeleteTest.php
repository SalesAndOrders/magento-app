<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Test\Unit\Plugin\Admin\System\Integration;

use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use SalesAndOrders\FeedTool\Plugin\Admin\System\Integration\Delete;

/**
 * Comment is required here
 */
class DeleteTest extends TestCase
{

    protected $deleteMock;

    protected $request;

    protected $storeManager;

    protected $storeMock;

    protected $webkook;

    protected $object;

    protected function setUp()
    {
        $this->deleteMock = $this->createMock(\Magento\Integration\Controller\Adminhtml\Integration\Delete::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->webkook = $this->createMock(\SalesAndOrders\FeedTool\Model\ResourceModel\WebHook::class);

        $this->object = $this->getMockBuilder(
            Delete::class
        )
            ->setMethods(
                [
                'getRequest'
                ]
            )
            ->setConstructorArgs(
                [
                    $this->storeManager,
                    $this->webkook
                ]
            )
            ->getMock();
    }

    public function testBeforeExecute()
    {
        $this->deleteMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->request->expects($this->any())
            ->method('getParam')
            ->with('id')
            ->will($this->returnValue(10));

        $this->assertNull($this->object->beforeExecute($this->deleteMock));
    }

    public function testAfterExecute()
    {
        $this->webkook->expects($this->any())
            ->method('uninstallAll')
            ->with(10)
            ->will($this->returnValue(true));
        $this->assertSame([], $this->object->afterExecute($this->deleteMock, []));
    }
}
