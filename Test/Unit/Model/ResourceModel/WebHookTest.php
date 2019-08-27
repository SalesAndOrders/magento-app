<?php

namespace SalesAndOrders\FeedTool\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use SalesAndOrders\FeedTool\Model\Integration\Activation;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

/**
 * Comment is required here
 */
class WebHookTest extends TestCase
{

    protected $object;

    protected $context;

    protected $integrationFactory;

    protected $integrationWebhook = null;

    protected $storeManager;

    protected $transport;

    protected $cacheModel;

    protected $integrationModel;

    protected $connectionMock;

    protected $resource;

    protected $selectMock;

    protected $connection;

    protected $storeMock;

    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $this->integrationFactory = $this->createMock(\Magento\Integration\Model\IntegrationFactory::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getBaseUrl', 'getCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->transport = $this->createMock(\SalesAndOrders\FeedTool\Model\Transport::class);
        $this->cacheModel = $this->createMock(\SalesAndOrders\FeedTool\Model\Cache::class);
        $this->integrationModel = $this->getMockBuilder(\Magento\Integration\Model\Integration::class)
            ->setMethods(['load', 'setSetupType', 'save', 'delete', 'getId', 'getName',
                'getStatus', 'setStatus', 'getConsumerId', 'setConsumerId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->integrationFactory->expects($this->any())->method('create')->with()->willReturn($this->integrationModel);
        $this->integrationModel->expects($this->any())->method('load')->with(Activation::INTEGRATION_NAME, 'name')
                ->willReturn($this->integrationModel);

        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->any())->method('select')->willReturn($select);

        $this->resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getConnection',
                    'getMainTable',
                    'getTable',
                    'deleteSessionsOlderThen',
                    'updateStatusByUserId'
                    ]
            )
            ->getMockForAbstractClass();

        $this->resource->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connection));

        $this->resource->expects($this->any())->method('getMainTable')->willReturn('table_test');
        $this->resource->expects($this->any())->method('getTable')->willReturn('test');

        $this->object = $this->getMockBuilder(
            WebHook::class
        )
            ->setMethods(
                [
                'getWebHookData',
                'getIntegration',
                'getConnection',
                'getWebHookWithUninstallUrl',
                'deleteWebHookByIntegration',
                'getAuthorizedWebhooks'
                ]
            )
            ->setConstructorArgs(
                [
                    $this->context,
                    $this->integrationFactory,
                    $this->storeManager,
                    $this->transport,
                    $this->cacheModel
                ]
            )
            //->disableOriginalConstructor()
            ->getMock();

        $this->object->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connection));

        $this->object->expects($this->any())
            ->method('getIntegration')
            ->will($this->returnValue($this->integrationModel));

        $this->object->expects($this->any())
            ->method('getWebHookData')
            ->will($this->returnValue([]));

        parent::setUp();
    }

    public function testGetIntegration()
    {
        $this->integrationModel->expects($this->any())->method('getId')->with()->willReturn(null);
        $this->integrationFactory->expects($this->any())->method('create')->with()->willReturn($this->integrationModel);
        $this->integrationModel->expects($this->any())->method('load')->with(Activation::INTEGRATION_NAME, 'name')
        ->willReturn($this->integrationModel);

        $this->assertEquals($this->integrationModel, $this->object->getIntegration());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAddIntegrationWebHook($data)
    {
        $this->integrationModel->expects($this->any())->method('getId')->with()->willReturn(10);
        $this->connection->expects($this->any())->method('insert')->will($this->returnValue($this->connection));
        $this->cacheModel->expects($this->any())->method('cleanCahes')->with(['config', 'block_html'])
            ->will($this->returnValue(true));
        $this->assertEquals(true, $this->object->addIntegrationWebHook($data, 1));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testUninstallAll($data)
    {
        $this->object->expects($this->any())->method('getWebHookWithUninstallUrl')
            ->will($this->returnValue((object)['id' => 10, 'uninstall_url' => 'some_url']));
        $this->object->expects($this->any())->method('deleteWebHookByIntegration')->with($data['integration_id'])
            ->will($this->returnValue(true));
        $this->storeManager->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getBaseUrl')->with()->willReturn('url');
        $this->storeMock->expects($this->any())->method('getCode')->with()->willReturn('default');
        $this->object->expects($this->any())->method('getAuthorizedWebhooks')->with()
            ->willReturn((object)['webhook_count' => 0]);

        $this->assertEquals(true, $this->object->uninstallAll($data['integration_id']));
    }

    public function dataProvider()
    {
        return [
            [
                ['store_code' => 'default', 'integration_id' => 10]
            ]
        ];
    }
}
