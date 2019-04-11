<?php

namespace SalesAndOrders\FeedTool\Block\Adminhtml\Form\Field;


use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use SalesAndOrders\FeedTool\Model\ResourceModel\Mapper\Collection as MapperCollection;
use SalesAndOrders\FeedTool\Model\Mapper as MapperModel;
use \Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use \Magento\Framework\App\Config\ScopeConfigInterface;


class FieldMapper extends Fieldset
{
    const ADMIN_STORE_CODE      = 'admin';

    /**
     * @var
     */
    protected $_dummyElement;
    /**
     * @var
     */
    protected $_fieldRenderer;
    /**
     * @var
     */
    protected $_statevalues;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManager;
    /**
     * @var mixed
     */
    protected $statusCollectionFactory;
    /**
     * @var mixed
     */
    protected $storesInterface;
    /**
     * @var MapperCollection
     */
    protected $mapperCollection;
    /**
     * @var Attribute
     */
    protected $attributeFactory;
    /**
     * @var
     */
    protected $stores;
    /**
     * @var
     */
    protected $fields;


    /**
     * FieldMapper constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param MapperCollection $mapperCollection
     * @param Attribute $attributeFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        MapperCollection $mapperCollection,
        Attribute $attributeFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->statusCollectionFactory = $this->objectManager->get('\Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory');
        $this->storesInterface = $this->objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $this->mapperCollection = $mapperCollection;
        $this->attributeFactory = $attributeFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context,$authSession,$jsHelper);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
        $fields = [];
        $this->fields = $this->mapperCollection->getItems();
        if ($this->fields) {
            foreach ($this->fields as $field) {
                $fields[] = ['label' => $field->getData('name'), 'value' => $field->getData('key')];
            }
        }

        $attributes[] = ['label' => __('Choose attribute'), 'value' => ''];
        $attribetesCollection = $this->attributeFactory->getCollection();
        if ($attribetesCollection->getItems()) {
            foreach ($attribetesCollection->getItems() as $item) {
                $attributes[] = ['label' => $item->getData('attribute_code'), 'value' => $item->getData('attribute_code')];
            }
        }

        $groups = [];
        if ($fields) {
            $i = 0;
            foreach ($fields as $field) {
                $identificator = $field['value'];
                $groups[] = array('name'=>$identificator,'label'=>$field['label'], 'value' => $identificator);
            }
        }

        asort($attributes);
        foreach ($groups as $group) {
            $html.= $this->_getFieldHtml($element, $group, $attributes);
        }

        return $html;

    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        $html = '<input id="' .
            $element->getHtmlId() .
            '-state" name="config_state[' .
            $element->getId() .
            ']" type="hidden" value="' .
            (int)$this->_isCollapseState(
                $element
            ) . '" />';
        $html .= '<legend>' . $element->getLegend() . '</legend>';

        $html .= $this->_getHeaderCommentHtml($element);

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if ($this->getRequest()->getParam('website') || $this->getRequest()->getParam('store')) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new \Magento\Framework\DataObject(array('show_in_default'=>1, 'show_in_website'=>1));
        }
        return $this->_dummyElement;
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->_layout->getBlockSingleton('Magento\Config\Block\System\Config\Form\Field');
        }
        return $this->_fieldRenderer;
    }

    /**
     * @return array
     */
    protected function _getStateValues()
    {
        $options_list=[];
        $this->_statevalues = $options_list;

        return $this->_statevalues;
    }

    /**
     * @param $fieldset
     * @param $group
     * @param $values
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $group, $values)
    {
        $group_id = $group['value'];
        $group_code = $group['label'];
        $e = $this->_getDummyElement();

        $field = $fieldset->addField(MapperModel::SELECT_PREFIX . $group_id, 'select',
            array(
                'name'          => 'groups['.MapperModel::SELECT_GRPOUP_NAME.'][fields]['.$group_id.'][value]',
                'label'         => $group_code,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($e),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e),
                'values'         => $values,
                'value'         => $this->scopeConfig->getValue(MapperModel::FIELDSET_PREFIX . $group_id, \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            ))->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}