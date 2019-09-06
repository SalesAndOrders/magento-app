<?php

namespace SalesAndOrders\FeedTool\Test\Unit\Block;

use Magento\Integration\Model\Integration;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use SalesAndOrders\FeedTool\Block\Adminhtml\Page\View;
use SalesAndOrders\FeedTool\Model\Integration\Activation;

/**
 * Comment is required here
 */
class ViewTest extends TestCase
{

    protected $context;

    protected $integrationFactory;

    protected $webHookModel;

    protected $activation;

    protected $configHelper;

    protected $storeManager;

    protected $storeMock;

    protected $storeId = 1;

    protected $reader;

    protected $object = null;

    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->integrationFactory = $this->createMock(\Magento\Integration\Model\IntegrationFactory::class);
        $this->webHookModel = $this->createMock(\SalesAndOrders\FeedTool\Model\ResourceModel\WebHook::class);
        $this->activation = $this->createMock(\SalesAndOrders\FeedTool\Model\Integration\Activation::class);
        $this->configHelper = $this->createMock(\SalesAndOrders\FeedTool\Helper\Config::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $this->reader = $this->createMock(\Magento\Framework\App\DeploymentConfig\Reader::class);

        $this->object = new View(
            $this->context,
            $this->integrationFactory,
            $this->webHookModel,
            $this->activation,
            $this->configHelper,
            $this->storeManager,
            $this->reader
        );
    }

    public function testGetIframeLoadUrl()
    {
        $this->configHelper->expects($this->any())->method('getIframeLoadUrl')
        ->will($this->returnValue('some_load_url'));
        $this->activation->expects($this->any())->method('getConsumer')
            ->will($this->returnValue($this->createMock(\Magento\Integration\Model\Oauth\Consumer::class)));
        $this->activation->expects($this->any())->method('getStoreBaseUrl')
        ->will($this->returnValue('https://baseUrl.com'));
        $this->activation->expects($this->any())->method('getHmac')->will($this->returnValue('some_hmac_str'));
        $result = $this->object->getIframeLoadUrl();
        $this->assertEquals('some_hmac_str', $result);
    }

    public function testGetIframeLinkData()
    {
        $integrationModel = $this->createMock(Integration::class);
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getCode')->with()->willReturn('default');
        $this->integrationFactory->expects($this->any())->method('create')->with()->willReturn($integrationModel);
        $integrationModel->expects($this->any())->method('load')->with(Activation::INTEGRATION_NAME, 'name')
            ->willReturn($integrationModel);
        $integrationModel->expects($this->any())->method('getId')->with()->willReturn(10);
        $this->webHookModel->expects($this->any())->method('getEnabledWebhooks')
            ->will($this->returnValue((object)['webhook_count' => 1]));
        $this->webHookModel->expects($this->any())->method('getAuthorizedWebhooks')
            ->will($this->returnValue((object)['webhook_count' => 1]));
        $this->webHookModel->expects($this->any())->method('getCustomWebHookData')->with(10)
            ->will($this->returnValue((object)['verify_url_endpoint'=>'some_url']));
        $integrationModel->expects($this->any())->method('delete')->with()->willReturnSelf();
        $this->testGetIframeLoadUrl();

        $this->activation->expects($this->any())->method('isValidURL')
            ->withAnyParameters()->will($this->returnValue(true));

        $this->assertArrayHasKey('url', $this->object->getIframeLinkData());
    }

    public function testGetAdminBaseUrl()
    {
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getBaseUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->reader->expects($this->any())->method('load')->willReturn(['backend' => ['frontName' => 'test']]);
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $storeMock->expects($this->any())->method('getBaseUrl')->will($this->returnValue('base_url'));
        $this->assertStringStartsWith('base_url', $this->object->getAdminBaseUrl());
    }
}
