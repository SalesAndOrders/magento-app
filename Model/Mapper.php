<?php

namespace SalesAndOrders\FeedTool\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Mapper extends AbstractModel implements IdentityInterface{

    const CACHE_TAG = 'perspective_attribute_mapper';

    const SELECT_GRPOUP_NAME    = 'field_mapper';
    const SELECT_PREFIX         = 'custom_options_field_mapper_';
    const FIELDSET_PREFIX       = 'custom_options/field_mapper/';

    protected $_cacheTag = 'perspective_attribute_mapper';

    protected $_eventPrefix = 'perspective_attribute_mapper';

    protected $scopeConfig;

    protected $mapperBlockField;

    protected $mapperCollection;

    public $mapper = [];

    protected function _construct()
    {
        $this->_init('SalesAndOrders\FeedTool\Model\ResourceModel\Mapper');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }

    public function getMapperToAttributes()
    {
        $result = [];
        $this->mapperToArray();
        if ($this->mapper) {
            foreach ($this->mapper as $mapperKey => $mapperValue) {
                $attr = $this->scopeConfig->getValue(self::FIELDSET_PREFIX . $mapperKey, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if ($attr) {
                    $result[$mapperKey] = $attr;
                }
            }
        }
        return $result;
    }

    public function mapperToArray()
    {
        $mapperCollection = $this->getCollection()->getItems();
        if ($mapperCollection) {
            foreach ($mapperCollection as $mapper) {
                if ($mapper->getData('key') && $mapper->getData('key')) {
                    $this->mapper[$mapper->getData('key')] = $mapper->getData('name');
                }
            }
        }
    }

}