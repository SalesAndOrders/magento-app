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
use Magento\Framework\App\DeploymentConfig\Reader;

/**
 * Comment is required here
 */
class View extends Template
{

    protected $integrationFactory;

    protected $webHookModel;

    protected $activationModel;

    protected $configHelper;

    protected $storeManager;

    protected $_configReader;

    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        WebHook $webHookModel,
        Activation $activation,
        Config $configHelper,
        StoreManagerInterface $storeManager,
        Reader $reader
    ) {
        $this->integrationFactory = $integrationFactory;
        $this->webHookModel = $webHookModel;
        $this->activationModel = $activation;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->_configReader = $reader;
        parent::__construct($context);
    }

    /**
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function getIframeLinkData()
    {
        $data = ['content' => false, 'url' => false];
        $store = $this->storeManager->getStore();
        $store = $store->getCode();
        $integration = $this->integrationFactory->create()->load(IntegrationActivation::INTEGRATION_NAME, 'name');
        if ($integration && $integration->getId()) {

            $enabledWebhooks = $this->webHookModel->getEnabledWebhooks();
            $authWebHooks = $this->webHookModel->getAuthorizedWebhooks();
            if ($enabledWebhooks && $enabledWebhooks->webhook_count > 0) {
                $webHook = $this->webHookModel->getCustomWebHookData($integration->getId());
                $data = [
                    'content' => 'login_iframe',
                    'url' => $webHook->verify_url_endpoint
                ];
            } else {
                $integration->delete();
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
    public function getIframeLoadUrl()
    {
        $loadUrl = $this->configHelper->getIframeLoadUrl();
        $consumer = $this->activationModel->getConsumer();
        $params = ['store_base_url' => $this->activationModel->getStoreBaseUrl()];
        $fullLoadUrl = $this->activationModel->getHmac($loadUrl, $consumer['secret'], $params);
        return $fullLoadUrl;
    }

    public function getAdminBaseUrl()
    {
        $config = $this->_configReader->load();
        $adminSuffix = $config['backend']['frontName'];
        $store = $this->storeManager->getStore();
        $baseUrl = $store->getBaseUrl();
        return  $baseUrl . $adminSuffix . '/';
    }
}
