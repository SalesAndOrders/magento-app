<?php

namespace SalesAndOrders\FeedTool\Block\Adminhtml\Page;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Integration\Model\IntegrationFactory;
use \SalesAndOrders\FeedTool\Model\Integration\Activation as IntegrationActivation;
use \SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;
use \SalesAndOrders\FeedTool\Model\Integration\Activation;
use \SalesAndOrders\FeedTool\Helper\Config;
use \Magento\Store\Model\StoreManagerInterface;

class View extends Template
{

    protected $integrationFactory;

    protected $webHookModel;

    protected $activationModel;

    protected $configHelper;

    protected $_storeManager;

    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        WebHook $webHookModel,
        Activation $activation,
        Config $configHelper,
        StoreManagerInterface $_storeManager
    )
    {
        $this->integrationFactory = $integrationFactory;
        $this->webHookModel = $webHookModel;
        $this->activationModel = $activation;
        $this->configHelper = $configHelper;
        $this->_storeManager = $_storeManager;
        parent::__construct($context);
    }

    /**
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function getIframeLinkData()
    {
        $data = ['content' => false, 'url' => false];
        $store = $this->_storeManager->getStore()->getCode();
        $ingegration = $this->integrationFactory->create()->load(IntegrationActivation::INTEGRATION_NAME, 'name');
        if ($ingegration && $ingegration->getId()) {

            $enabledWebhooks = $this->webHookModel->getEnabledWebhooks();
            $authWebHooks = $this->webHookModel->getAuthorizedWebhooks();
            if ($enabledWebhooks && $enabledWebhooks->webhook_count > 0) {
                $webHook = $this->webHookModel->getCustomWebHookData($ingegration->getId());
                $data = [
                    'content' => 'login_iframe',
                    'url' => $webHook->verify_url_endpoint
                ];
            }else{
                $ingegration->delete();
            }

            if ($authWebHooks && $authWebHooks->webhook_count > 0) {
                $data = [
                    'content' => 'load_iframe',
                    'url' => $this->getIframeLoadUrl()
                ];
            }

        }

        return $data;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     */
    private function getIframeLoadUrl()
    {
        $loadUrl = $this->configHelper->getIframeLoadUrl();
        $consumer = $this->activationModel->getConsumer();
        $params = ['store_base_url' => $this->activationModel->getStoreBaseUrl()];
        $fullLoadUrl = $this->activationModel->getHmac($loadUrl, $consumer['secret'], $params);
        return $fullLoadUrl;
    }
}

