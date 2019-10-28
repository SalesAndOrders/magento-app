<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Test\Unit\Model;

use \PHPUnit\Framework\TestCase;
use \SalesAndOrders\FeedTool\Model\Integration\Activation;

/**
 * Comment is required here
 */
class ActivationTest extends TestCase
{
    protected $object;

    protected $context;

    protected $integrationFactory;

    protected $oauthService;

    protected $authorizationService;

    protected $token;

    protected $tokenFactory;

    protected $storeManager;

    protected $authSession;

    protected $_dataHelper;

    protected $_httpClient;

    protected $webHookModel;

    protected $transport;

    protected $logger;

    protected $integrationManager;

    protected $cacheModel;

    protected $integrationModel;

    protected $consumerModel;

    protected $activation;

    public function setUp()
    {
        $integrationName = 'sales_and_orders';

        $this->context = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $this->integrationFactory = $this->createMock(\Magento\Integration\Model\IntegrationFactory::class);
        $this->oauthService = $this->createMock(\Magento\Integration\Model\OauthService::class);
        $this->authorizationService = $this->createMock(\Magento\Integration\Model\AuthorizationService::class);
        $this->token = $this->createMock(\Magento\Integration\Model\Oauth\Token::class);
        $this->tokenFactory = $this->createMock(\Magento\Integration\Model\Oauth\TokenFactory::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->authSession = $this->createMock(\Magento\Backend\Model\Auth\Session::class);
        $this->_dataHelper = $this->createMock(\Magento\Integration\Helper\Oauth\Data::class);
        $this->_httpClient = $this->createMock(\Magento\Framework\HTTP\ZendClient::class);
        $this->webHookModel = $this->createMock(\SalesAndOrders\FeedTool\Model\ResourceModel\WebHook::class);
        $this->transport = $this->createMock(\SalesAndOrders\FeedTool\Model\Transport::class);
        $this->logger = $this->createMock(\SalesAndOrders\FeedTool\Model\Logger::class);
        $this->integrationManager = $this->createMock(\Magento\Integration\Model\ConfigBasedIntegrationManager::class);
        $this->cacheModel = $this->createMock(\SalesAndOrders\FeedTool\Model\Cache::class);
        $this->activation = $this->createMock(\SalesAndOrders\FeedTool\Model\Integration\Activation::class);
        $this->integrationModel = $this->getMockBuilder(\Magento\Integration\Model\Integration::class)
            ->setMethods(['load', 'setSetupType', 'save', 'delete', 'getId',
            'getName', 'getStatus', 'setStatus', 'getConsumerId', 'setConsumerId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->consumerModel = $this->createMock(\Magento\Integration\Model\Oauth\Consumer::class);
        $this->integrationFactory->expects($this->any())->method('create')->with()->willReturn($this->integrationModel);
        $this->integrationModel
        ->expects($this->any())->method('load')->with($integrationName, 'name')
        ->willReturn($this->integrationModel);

        $this->object = new Activation(
            $this->context,
            $this->integrationFactory,
            $this->oauthService,
            $this->authorizationService,
            $this->token,
            $this->tokenFactory,
            $this->storeManager,
            $this->authSession,
            $this->_dataHelper,
            $this->_httpClient,
            $this->webHookModel,
            $this->transport,
            $this->logger,
            $this->integrationManager,
            $this->cacheModel
        );
    }

    public function testGetIntegration()
    {
        $integrationName = 'sales_and_orders';
        $this->integrationFactory->expects($this->any())->method('create')->willReturn($this->integrationModel);
        $this->integrationModel
        ->expects($this->once())->method('load')->with($integrationName, 'name')
        ->willReturn($this->integrationModel);
        $this->assertSame($this->integrationModel, $this->object->getIntegration());
        return $this->integrationModel;
    }

    /**
     * CreateIntegration method
     *
     * @var Activation
     */
    public function testCreateIntegration()
    {
        $integrationName = 'sales_and_orders';
        $this->integrationModel->expects($this->any())->method('getId')->with()->willReturn(10);
        $this->integrationManager
        ->expects($this->any())->method('processIntegrationConfig')->with([$integrationName])->willReturn([]);
        $this->integrationFactory->expects($this->any())->method('create')->with()->willReturn($this->integrationModel);
        $this->integrationModel
        ->expects($this->any())->method('load')->with($integrationName, 'name')
        ->willReturn($this->integrationModel)
        ;
        $this->integrationModel
        ->expects($this->any())->method('setSetupType')
        ->with(2)->willReturn($this->integrationModel);
        $this->integrationModel->expects($this->any())->method('save')->with()->willReturn($this->integrationModel);
        $integration = $this->object->createIntegration();
        $this->assertSame(true, $integration);
        return $integration;
    }

    /**
     * getConsumer method
     *
     * @var \Magento\Integration\Model\Oauth\Consumer
     */
    public function testGetConsumer()
    {

        $this->integrationModel->expects($this->any())->method('getId')->with()->willReturn(10);
        $this->integrationModel->expects($this->any())->method('getConsumerId')->with()->willReturn(2);
        $this->oauthService
        ->expects(
            $this->any()
        )->method('loadConsumer')->withAnyParameters()->willReturn($this->consumerModel);
        $this->consumerModel->expects($this->any())->method('getId')->with()->willReturn(3);
        $this->object->getIntegration();
        $this->assertSame($this->consumerModel, $this->object->getConsumer());
    }

    public function testActivateIntegration()
    {
        $this->testGetConsumer();
        $this->integrationModel->expects($this->any())->method('getId')->with()->willReturn(10);
        $this->integrationModel->expects($this->any())->method('getConsumerId')->with()->willReturn('2');
        $this->oauthService
        ->expects($this->any())->method('loadConsumer')->withAnyParameters()->willReturn($this->consumerModel);
        $this->integrationModel->expects($this->once())->method('getStatus')->with()->willReturn(0);
        $this->logger->expects($this->at(0))->method('log')->with('Activating ...')->willReturn([]);
        $this->integrationModel
        ->expects($this->once())->method('setStatus')->with(1)->willReturn($this->integrationModel);
        $this->integrationModel
        ->expects($this->once())->method('setSetupType')->with(2)->willReturn($this->integrationModel);
        $this->consumerModel->expects($this->any())->method('getId')->with()->willReturn($this->consumerModel);
        $this->integrationModel
        ->expects(
            $this->once()
        )->method('setConsumerId')->withAnyParameters()->willReturn($this->integrationModel);
        $this->integrationModel->expects($this->once())->method('save')->with()->willReturn($this->integrationModel);
        $this->logger->expects($this->at(1))->method('log')->withAnyParameters()->willReturn([]);
        $this->assertSame(true, $this->object->activateIntegration());
    }

    public function testDeleteIntegration()
    {
        $this->integrationModel->expects($this->any())->method('getName')->with()->willReturn('test');
        $this->webHookModel->expects($this->any())->method('deleteWebHook')->with()->willReturn(true);
        $this->integrationModel->expects($this->any())->method('delete')->with()->willReturn(true);
        $this->integrationModel->expects($this->any())->method('getId')->with()->willReturn(10);
        $this->assertSame(true, $this->object->deleteIntegration());
    }

    public function testDeactivateIntegration()
    {
        $this->integrationModel->expects($this->any())->method('getStatus')->with()->willReturn('1');
        $this->integrationModel->expects(
            $this->any()
        )->method('setStatus')->with('0')->willReturn($this->integrationModel);
        $this->integrationModel->expects($this->any())->method('save')->with()->willReturn($this->integrationModel);
        $this->integrationModel->expects($this->any())->method('getId')->with()->willReturn(10);
        $this->webHookModel->expects($this->any())->method('deleteWebHook')->with()->willReturn(true);
        $this->assertSame(true, $this->object->deactivateIntegration());
    }
}
