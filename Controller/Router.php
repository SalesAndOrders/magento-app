<?php

namespace SalesAndOrders\FeedTool\Controller;

/**
 * Comment is required here
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;
    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * @param \Magento\Framework\App\ActionFactory     $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
    }
    /**
     * Validate and Match
     *
     * @param  \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if ($request->getModuleName() == 'salesandorders') {
            return;
        }
        $identifier = trim($request->getPathInfo(), '/');
        if (strpos($identifier, 'webhooks/') !== false) {
            $params = $this->getWebhookDeleteParams($identifier);
            $request->setModuleName('salesandorders')->setControllerName('webhook')->setActionName('delete')
                ->setParam('webhook', isset($params['webhooks']) ? $params['webhooks'] : '')
                ->setParam('store', isset($params['store']) ? $params['store'] : '');
        } else {
            return;
        }

        return $this->actionFactory->create(
            Magento\Framework\App\Action\Forward::class,
            ['request' => $request]
        );
    }

    /**
     * @param  string $identifier
     * @return array
     */
    protected function getWebhookDeleteParams($identifier = '/')
    {
        $dataExplode = explode('/', $identifier);
        $data = array_filter($dataExplode);
        if (isset($data[0]) && isset($data[1]) && isset($data[2]) && isset($data[3])) {
            $result = [$data[0] => $data[1], $data[2] => $data[3]];
        } else {
            $result = [];
        }
        return $result;
    }
}
