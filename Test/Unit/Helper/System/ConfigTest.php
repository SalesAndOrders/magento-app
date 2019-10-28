<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Test\Unit\Helper\System;

use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use SalesAndOrders\FeedTool\Helper\System\Config;

/**
 * Comment is required here
 */
class ConfigTest extends TestCase
{

    protected $context;

    protected $objectManager;

    protected $object;

    protected $scopeConfig;

    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfigMock));

        $this->object = $this->getMockBuilder(
            Config::class
        )
            ->setMethods(
                [
                'getSecureBaseUrl'
                ]
            )
            ->setConstructorArgs(
                [
                    $this->context,
                    $this->objectManager
                ]
            )
            //->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetStoreCode()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_SYSTEM_STORE_CODE, ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue('test3'));

        $this->assertSame('test3', $this->object->getStoreCode());
    }

    public function testGetStoreName()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_SYSTEM_STORE_NAME, ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue('test3'));

        $this->assertSame('test3', $this->object->getStoreName());
    }

    public function testGetCountry()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_SYSTEM_COUNTRY, ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue('test3'));

        $this->assertSame('test3', $this->object->getCountry());
    }

    public function testGetBaseCurrency()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_SYSTEM_BASE_CURRENCY, ScopeInterface::SCOPE_STORE)
            ->will($this->returnValue('test3'));

        $this->assertSame('test3', $this->object->getBaseCurrency());
    }
}
