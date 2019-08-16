<?php

namespace SalesAndOrders\FeedTool\Test\Unit\Ui\Component\Listing\Column;

use \PHPUnit\Framework\TestCase;
use SalesAndOrders\FeedTool\Ui\Component\Listing\Column\SalesAndOrdersActions;

class SalesAndOrdersActionsTest extends TestCase
{

    protected $object;

    protected $context;

    protected $urlComponentFactory;

    protected $urlBuilder;

    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $this->urlComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);

        $this->object = new SalesAndOrdersActions(
            $this->context,
            $this->urlComponentFactory,
            $this->urlBuilder
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testPrepareDataSource($data)
    {
        $itemName = 'itemName';
        $this->object->setData('name', $itemName);
        $this->urlBuilder->expects($this->any())->method('getUrl')->with()->will($this->returnValue('some_url'));
        $dataSource = $this->object->prepareDataSource($data);
        $this->assertArrayHasKey('delete', $dataSource['data']['items'][0][$itemName]);
    }

    public function dataProvider()
    {
        return [[
                ['data' =>
                    array (
                        'items' =>
                            array (
                                0 =>
                                    array (
                                        'id_field_name' => 'id',
                                        'id' => '30',
                                        'integration_id' => '50',
                                        'consumer_id' => '100',
                                        'is_oath_authorized' => '0',
                                        'verify_url_endpoint' => 'test',
                                        'store_code' => 'default1',
                                        'is_deleted' => '0',
                                        'products_webhook_url' => NULL,
                                        'account_update_url' => NULL,
                                        'uninstall_url' => NULL,
                                        'store_id' => '1',
                                        'store_name' => 'Default Store View',
                                        'orig_data' => NULL,
                                    ),
                            ),
                        'totalRecords' => 1,
                    )]
        ]];
    }

}