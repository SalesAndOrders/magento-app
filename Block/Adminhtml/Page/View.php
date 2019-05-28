<?php

namespace SalesAndOrders\FeedTool\Block\Adminhtml\Page;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Integration\Model\IntegrationFactory;
use \SalesAndOrders\FeedTool\Model\Integration\Activation as IntegrationActivation;
use \SalesAndOrders\FeedTool\Model\WebHook;
use \SalesAndOrders\FeedTool\Model\Integration\Activation;
use \SalesAndOrders\FeedTool\Helper\Config;

class View extends Template
{

    protected $integrationFactory;

    protected $webHookModel;

    protected $activationModel;

    protected $configHelper;

    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        WebHook $webHookModel,
        Activation $activation,
        Config $configHelper
    )
    {
        $this->integrationFactory = $integrationFactory;
        $this->webHookModel = $webHookModel;
        $this->activationModel = $activation;
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    /**
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function getIframeLinkData()
    {
        $data = [
            'content' => false,
            'url' => false
        ];
        $ingegration = $this->integrationFactory->create()->load(IntegrationActivation::INTEGRATION_NAME, 'name');
        if ($ingegration && $ingegration->getId()) {
            $webHook = $this->webHookModel->getWebHookData($ingegration->getId());
            if ($webHook && $webHook->verify_url_endpoint && $webHook->is_oath_authorized == 0) {
                $data = [
                    'content' => 'login_iframe',
                    'url' => $webHook->verify_url_endpoint
                ];
            } elseif ($webHook && $webHook->is_oath_authorized == 1) {
                $data = [
                    'content' => 'load_iframe',
                    'url' => $this->getIframeLoadUrl()
                ];
            } else {
                $data = [
                    'content' => false,
                    'url' => false
                ];
            }
        }

        return $data;
    }

    private function getIframeLoadUrl()
    {
        $loadUrl = $this->configHelper->getIframeLoadUrl();
        $consumer = $this->activationModel->getConsumer();
        $params = ['store_base_url' => $this->activationModel->getStoreBaseUrl()];
        $fullLoadUrl = $this->activationModel->getHmac($loadUrl, $consumer['secret'], $params);
        return $fullLoadUrl;
    }
}

