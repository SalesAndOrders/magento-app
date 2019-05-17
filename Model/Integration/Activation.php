<?php

namespace SalesAndOrders\FeedTool\Model\Integration;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Integration\Model\IntegrationFactory;
use \Magento\Integration\Model\OauthService;
use \Magento\Integration\Model\AuthorizationService;
use \Magento\Integration\Model\Oauth\Token;


class Activation extends AbstractDb
{

    public $integrationName = 'sales_and_order';

    public $consumerName = 'test_name';

    protected $integrationFactory;

    protected $oauthService;

    protected $authorizationService;

    protected $token;


    public function __construct(
        Context $context,
        IntegrationFactory $integrationFactory,
        OauthService $oauthService,
        AuthorizationService $authorizationService,
        Token $token
    )
    {
        $this->integrationFactory = $integrationFactory;
        $this->oauthService = $oauthService;
        $this->authorizationService = $authorizationService;
        $this->token = $token;
        parent::__construct($context);
    }

    public function _construct()
    {
        // TODO: Implement _construct() method.
    }

    public function activateIntegration()
    {
        $integration = $this->integrationFactory->create()->load($this->integrationName, 'name');
        //$integration->delete();
        if ($integration->getStatus() == '0') {
            $integration->setStatus(1);

            $consumer = $this->oauthService->createConsumer(['name' => $this->consumerName]);
            $consumerId = $consumer->getId();
            $integration->setConsumerId($consumer->getId());
            $integration->save();

            $this->authorizationService->grantAllPermissions($integration->getId());

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
}