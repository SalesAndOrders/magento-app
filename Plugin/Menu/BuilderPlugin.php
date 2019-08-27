<?php

namespace SalesAndOrders\FeedTool\Plugin\Menu;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\ItemFactory;
use SalesAndOrders\FeedTool\Model\Integration\Activation;
use SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

/**
 * Comment is required here
 */
class BuilderPlugin
{
    protected $menuItemFactory;

    protected $menu;

    protected $integrationActivation;

    protected $webHookModel;

    public function __construct(
        ItemFactory $menuItemFactory,
        Menu $menu,
        Activation $integrationActivation,
        WebHook $webHookModel
    ) {
        $this->menuItemFactory = $menuItemFactory;
        $this->menu = $menu;
        $this->integrationActivation = $integrationActivation;
        $this->webHookModel = $webHookModel;
    }

    public function afterGetResult(Builder $subject, Menu $menu)
    {
        $title = 'Install';
        $webhooks = $this->webHookModel->getEnabledWebhooks();
        if ($webhooks && $webhooks->webhook_count > 0) {
            $title = 'Log In';
        }

        $parent = 'SalesAndOrders_FeedTool::integration_module';
        $item = $this->menuItemFactory->create(
            [
            'data' => [
                'parent_id' => $parent,
                'id' => 'SalesAndOrders_FeedTool::integration',
                'title' => $title,
                'resource' => 'SalesAndOrders_FeedTool::integration_module',
                'action' => 'integration_module/page/view/',
                'sortOrder' => 71,
                'module' => 'SalesAndOrders_FeedTool'
            ]
            ]
        );
        $menu->add($item, $parent);

        $item = $this->menuItemFactory->create(
            [
            'data' => [
                'parent_id' => $parent,
                'id' => 'SalesAndOrders_FeedTool::accounts',
                'title' => 'Accounts',
                'resource' => 'SalesAndOrders_FeedTool::integration_module',
                'action' => 'integration_module/page/accounts/',
                'sortOrder' => 72,
                'module' => 'SalesAndOrders_FeedTool'
            ]
            ]
        );
        $menu->add($item, $parent);

        return $menu;
    }
}
