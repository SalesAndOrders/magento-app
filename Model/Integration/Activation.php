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


class Activation extends AbstractDb
{

    /**
     * @var string
     */
    public $integrationName = 'sales_and_order';

    /**
     * @var string
     */
    public $consumerName = 'test_name';

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


    protected $_dataHelper;


    protected $_httpClient;
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
     * @param Context $context
     * @param IntegrationFactory $integrationFactory
     * @param OauthService $oauthService
     * @param AuthorizationService $authorizationService
     * @param Token $token
     * @param TokenFactory $tokenFactory
     * @param StoreManagerInterface $storeManager
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
        ZendClient $_httpClient
    )
    {
        $this->integrationFactory = $integrationFactory;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->token = $token;
        $this->_tokenFactory = $tokenFactory;
        $this->_storeManager = $storeManager;
        $this->authSession = $authSession;
        $this->_dataHelper = $_dataHelper;
        $this->_httpClient = $_httpClient;

        $this->integration = $this->integrationFactory->create()->load($this->integrationName, 'name');
        $this->currentUser = $this->authSession->getUser();
        parent::__construct($context);
    }


    public function _construct()
    {
        // TODO: Implement _construct() method.
    }

    public function runActiovation()
    {
        $endPointUrl = $this->integration->getEndpoint();
        $this->createConsumer();
        $data = $this->getData();
        $response = $this->sendData($endPointUrl, $data);
        return $response;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Oauth\Exception
     */
    public function createConsumer()
    {
        $this->consumer = $this->oauthService->createConsumer(['name' => $this->consumerName]);
        return true;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activateIntegration()
    {
        if ($this->integration->getStatus() == '0') {
            $this->integration->setStatus(1);


            $consumerId = $this->consumer->getId();
            $this->integration->setConsumerId($this->consumer->getId());
            $this->integration->save();

            $this->authorizationService->grantAllPermissions($this->integration->getId());

            $token = $this->token;
            $uri = $token->createVerifierToken($consumerId);
            $token->setType('access');
            $token->save();
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function deleteIntegration()
    {
        if ($this->integration && $this->integration->getName()) {
            $this->integration->delete();
            return true;
        }
        return false;
    }

    public function deactivateIntegration()
    {
        if ($this->integration->getStatus() == '1') {
            $this->integration->setStatus(0);
            $this->integration->save();
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


    public function sendData($endpointUrl, $postData = [])
    {
        $curl = curl_init();

        $curlOptions = array(
            CURLOPT_URL => $endpointUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        );

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $err = curl_error($curl);
        curl_close($curl);
        return ['response' => $response, 'err' => $err];
    }
}