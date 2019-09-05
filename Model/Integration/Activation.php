<?php

namespace SalesAndOrders\FeedTool\Model\Integration;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Integration\Model\IntegrationFactory;
use \Magento\Integration\Model\OauthService;
use \Magento\Integration\Model\AuthorizationService;
use \Magento\Integration\Model\Oauth\Token;
use \Magento\Integration\Model\Oauth\TokenFactory as TokenFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Backend\Model\Auth\Session;
use \Magento\Integration\Helper\Oauth\Data as IntegrationOauthHelper;
use \Magento\Framework\HTTP\ZendClient;
use \SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;
use \SalesAndOrders\FeedTool\Model\Transport;
use \SalesAndOrders\FeedTool\Model\Logger;
use Magento\Integration\Model\ConfigBasedIntegrationManager;
use SalesAndOrders\FeedTool\Model\Cache;

/**
 * Comment is required here
 */
class Activation extends AbstractDb
{

    const INTEGRATION_NAME = 'sales_and_orders';

    const END_POINT_SUCCESS_CODE = 200;
    /**
     * @var string
     */
    public $integrationName = 'sales_and_orders';
    /**
     * @var string
     */
    public $consumerName = 'sales_and_orders_consumer';
    /**
     * @var IntegrationFactory
     */
    protected $integrationFactory;
    /**
     * @var OauthService
     */
    protected $oauthService;
    /**
     * @var AuthorizationService
     */
    protected $authorizationService;
    /**
     * @var Token
     */
    protected $token;
    /**
     * @var TokenFactory
     */
    protected $_tokenFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var Session
     */
    protected $authSession;
    /**
     * @var IntegrationOauthHelper
     */
    protected $_dataHelper;
    /**
     * @var ZendClient
     */
    protected $_httpClient;
    /**
     * @var Transport
     */
    protected $transport;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var WebHook
     */
    protected $webHookModel;
    /**
     * @var ConfigBasedIntegrationManager
     */
    protected $integrationManager;
    /**
     * @var Cache
     */
    protected $cacheModel;
    /**
     * @var \Magento\Integration\Model\Integration|null
     */
    protected $integration = null;
    /**
     * @var null
     */
    protected $consumer = null;
    /**
     * @var null
     */
    protected $currentUser = null;
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Activation constructor.
     *
     * @param Context                       $context
     * @param IntegrationFactory            $integrationFactory
     * @param OauthService                  $oauthService
     * @param AuthorizationService          $authorizationService
     * @param Token                         $token
     * @param TokenFactory                  $tokenFactory
     * @param StoreManagerInterface         $storeManager
     * @param Session                       $authSession
     * @param IntegrationOauthHelper        $_dataHelper
     * @param ZendClient                    $_httpClient
     * @param WebHook                       $webHookModel
     * @param Transport                     $transport
     * @param Logger                        $logger
     * @param ConfigBasedIntegrationManager $integrationManager
     * @param Cache                         $cacheModel
     */
    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        OauthService $oauthService,
        AuthorizationService $authorizationService,
        Token $token,
        TokenFactory $tokenFactory,
        StoreManagerInterface $storeManager,
        Session $authSession,
        IntegrationOauthHelper $_dataHelper,
        ZendClient $_httpClient,
        WebHook $webHookModel,
        Transport $transport,
        Logger $logger,
        ConfigBasedIntegrationManager $integrationManager,
        Cache $cacheModel
    ) {
        $this->integrationFactory = $integrationFactory;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->token = $token;
        $this->_tokenFactory = $tokenFactory;
        $this->_storeManager = $storeManager;
        $this->authSession = $authSession;
        $this->_dataHelper = $_dataHelper;
        $this->_httpClient = $_httpClient;
        $this->webHookModel = $webHookModel;
        $this->transport = $transport;
        $this->logger = $logger;
        $this->integrationManager = $integrationManager;
        $this->cacheModel = $cacheModel;

        $this->integration = $this->getIntegration();
        $this->currentUser = $this->authSession->getUser();
        parent::__construct($context);
    }

    public function _construct()
    {
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function runActivation()
    {
        if (!$this->createIntegration()) {
            return  false;
        }

        $logger = $this->logger->create('run_activation', 'oath');
        $endPointUrl = $this->integration->getEndpoint();
        $this->getConsumer();
        $data = $this->getData();
        $this->logger->log('Try send data to ' . $endPointUrl . ' URL');
        $this->logger->log($data);
        $response = $this->transport->sendData($endPointUrl, $data);
        $this->logger->log('Endpoint send response:');
        $result = json_decode($response['response']);
        $this->logger->log($result);
        if (isset($result->status) && $result->status == self::END_POINT_SUCCESS_CODE) {
            if ($this->isValidURL($result->detail)) {
                $this->logger->log('Try to activation integration');
                $this->activateIntegration();
                $this->logger->log('Creating webhook field');
                $this->webHookModel->addIntegrationWebHook(['verify_url_endpoint' => $result->detail], 0);
                $this->logger->log('Webhook field created');
                $this->logger->log('End activationg');
                // clear cache
                $this->cacheModel->cleanCahes(['config', 'block_html']);
                $resultData = $result->detail;
            } else {
                $this->deleteIntegration();
                $this->logger->log('Error, verify_url_endpoint is not URL');
                $resultData = false;
            }
        } else {
            $this->deleteIntegration();
            $this->logger->log('Error from endpoint');
            $resultData = false;
        }
        return $resultData;
    }

    /**
     * @return bool
     *
     * Magento\Integration\Model\Integration
     */
    public function createIntegration()
    {
        if (!$this->integration || !$this->integration->getId()) {
            $this->integrationManager->processIntegrationConfig([$this->integrationName]);
            $this->integration = $this->integrationFactory->create()->load($this->integrationName, 'name');
            $this->integration->setSetupType(2);
            $this->integration->save();
        }
        if (!$this->integration || !$this->integration->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function getConsumer()
    {
        if ($this->integration && $this->integration->getId()) {
            $consumer = $this->oauthService->loadConsumer($this->integration->getConsumerId());
            if ($consumer && $consumer->getId()) {
                $this->consumer = $consumer;
            } else {
                $this->consumer = $this->oauthService->createConsumer(['name' => $this->consumerName]);
                $this->integration->setConsumerId($this->consumer->getId());
                $this->integration->save();
            }
        } else {
            $this->consumer = $this->oauthService->createConsumer(['name' => $this->consumerName]);
        }
        return $this->consumer;
    }

    /**
     * @return \Magento\Integration\Model\Integration|null
     */
    public function getIntegration()
    {
        $this->integration = $this->integrationFactory->create()->load($this->integrationName, 'name');
        return $this->integration;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activateIntegration()
    {
        if ($this->integration->getStatus() == '0') {
            $this->logger->log('Activating ...');
            $this->integration->setStatus(1);
            $this->integration->setSetupType(2);

            $consumerId = $this->consumer->getId();
            $this->integration->setConsumerId($this->consumer->getId());
            $this->integration->save();

            $this->logger->log('Try to add permission');
            $this->authorizationService->grantAllPermissions($this->integration->getId());

            $this->logger->log('Create token');
            $token = $this->token;
            $uri = $token->createVerifierToken($consumerId);
            $token->setType('access');
            $token->save();
            $result = true;
        } else {
            $this->logger->log('Cant activate the integration? already activated');
            $result = false;
        }
        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function deleteIntegration($clearCaches = false)
    {
        if ($this->integration && $this->integration->getName()) {
            $this->webHookModel->deleteWebHook($this->integration->getId());
            $this->integration->delete();
            if ($clearCaches) {
                $this->cacheModel->cleanCahes(['config', 'block_html']);
            }
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function deactivateIntegration()
    {
        if ($this->integration->getStatus() == '1') {
            $this->integration->setStatus(0);
            $this->integration->save();
            $this->webHookModel->deleteWebHook($this->integration->getId());
            return true;
        }
        return false;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function getData()
    {
        $consumer = $this->oauthService->loadConsumer($this->consumer->getId());
        $consumerData = $consumer->getData();

        $storeBaseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $verifier = $this->_tokenFactory->create()->createVerifierToken($this->consumer->getId());
        $data = [
                'oauth_consumer_key' => $consumerData['key'],
                'oauth_consumer_secret' => $consumerData['secret'],
                'store_base_url' => $storeBaseUrl,
                'oauth_verifier' => $verifier->getVerifier(),
                'first_name' => $this->currentUser->getFirstName(),
                'last_name' => $this->currentUser->getLastName(),
                'email' => $this->currentUser->getEmail()
        ];
        $this->data = $data;
        return $data;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * @param  $url
     * @param  $secret
     * @param  $queryParams
     * @return string
     */
    public function getHmac($url, $secret, $queryParams)
    {
        //first get params from url string
        $paramsStr = parse_url($url, PHP_URL_QUERY);
        parse_str($paramsStr, $decomposedParams);

        if (!empty($decomposedParams)) {
            $queryParams = array_merge($decomposedParams, $queryParams);
        }

        $cleanUrl = explode("?", $url)[0];

        $dataArray = [];
        foreach ($queryParams as $field => $value) {
            $dataArray[] = urlencode($field) . '=' . urlencode($value);
        }

        sort($dataArray);
        $dataString = implode("&", $dataArray);
        $hmac = hash_hmac('sha256', $dataString, $secret);

        $queryParams["hmac"] = $hmac;
        $queryPart = http_build_query(
            $queryParams,
            null,
            '&',
            PHP_QUERY_RFC3986
        );

        $queryString = $cleanUrl . '?' . $queryPart;
        // @todo delete this line, it's for testing only
        $compareQueryString = $cleanUrl . '?' . $dataString . '&hmac=' . $hmac;
        return $queryString;
    }

    public function isValidURL($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return false;
        }
        return true;
    }
}
