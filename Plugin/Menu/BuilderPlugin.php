<?php

namespace SalesAndOrders\FeedTool\Plugin\Menu;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\ItemFactory;
use SalesAndOrders\FeedTool\Model\Integration\Activation;

class BuilderPlugin
{
    protected $menuItemFactory;

    protected $menu;

    protected $integrationActivation;

    public function __construct(
        ItemFactory $menuItemFactory,
        Menu $menu,
        Activation $integrationActivation
    ) {
        $this->menuItemFactory = $menuItemFactory;
        $this->menu = $menu;
        $this->integrationActivation = $integrationActivation;
    }

    public function afterGetResult(Builder $subject, Menu $menu)
    {
        $parent = 'SalesAndOrders_FeedTool::integration_module';
        $item = $this->menuItemFactory->create([
            'data' => [
                'parent_id' => $parent,
                'id' => 'SalesAndOrders_FeedTool::integration',
                'title' => 'Log In',
                'resource' => 'SalesAndOrders_FeedTool::integration_module',
                'action' => 'integration_module/page/view/',
                'sortOrder' => 71,
                'module' => 'SalesAndOrders_FeedTool'
            ]
        ]);
        $menu->add($item, $parent);

        $item = $this->menuItemFactory->create([
            'data' => [
                'parent_id' => $parent,
                'id' => 'SalesAndOrders_FeedTool::accounts',
                'title' => 'Accounts',
                'resource' => 'SalesAndOrders_FeedTool::integration_module',
                'action' => 'integration_module/page/accounts/',
                'sortOrder' => 72,
                'module' => 'SalesAndOrders_FeedTool'
            ]
        ]);
        $menu->add($item, $parent);

        return $menu;
    }
}
