<?php

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Condition;

class Product extends AbstractModel
{
    protected $_entityAttributeValues = null;

    protected $_isUsedForRuleProperty = 'is_used_for_promo_rules';

    protected $_arrayInputTypes = [];

    protected $_customFiltersCache = [];

    private \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper;
    private \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\CustomFilterFactory $customFilterFactory;
    private \Magento\Backend\Model\UrlInterface $url;
    private \Magento\Eav\Model\Config $config;
    private \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection;
    private \Magento\Catalog\Model\ProductFactory $productFactory;
    private \Magento\Framework\Locale\FormatInterface $localeFormat;

    public function __construct(
        \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\CustomFilterFactory $customFilterFactory,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Eav\Model\Config $config,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->url = $url;
        $this->config = $config;
        $this->attrSetCollection = $attrSetCollection;
        $this->productFactory = $productFactory;
        $this->localeFormat = $localeFormat;
        $this->customFilterFactory = $customFilterFactory;
        parent::__construct($context, $data);
    }

    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return bool
     */
    public function validate(\Magento\Framework\DataObject $object)
    {
        $attrCode = $this->getAttribute();

        if ($this->isFilterCustom($attrCode)) {
            $value = $this->getCustomFilterInstance($attrCode)->getValueByProductInstance($object);

            return $this->validateAttribute($value);
        }

        if ('category_ids' === $attrCode) {
            return $this->validateAttribute($object->getAvailableInCategories());
        }

        if (!isset($this->_entityAttributeValues[$object->getId()])) {
            if (!$object->getResource()) {
                return false;
            }
            $attr = $object->getResource()->getAttribute($attrCode);

            if ($attr && $attr->getBackendType() === 'datetime' && !is_int($this->getValue())) {
                $oldValue = $this->getValue();
                $this->setValue(
                    (int)\M2E\TikTokShop\Helper\Date::createDateGmt($this->getValue())->format('U')
                );
                $value = (int)\M2E\TikTokShop\Helper\Date::createDateGmt($object->getData($attrCode))->format('U');
                $result = $this->validateAttribute($value);
                $this->setValue($oldValue);

                return $result;
            }

            if ($attr && $attr->getFrontendInput() === 'multiselect') {
                $value = $object->getData($attrCode);
                $value = strlen((string)$value) ? explode(',', $value) : [];

                return $this->validateAttribute($value);
            }

            return $this->validateAttribute($object->getData($attrCode));
        } else {
            $productStoreId = $object->getData('store_id');
            if (
                $productStoreId === null ||
                !isset($this->_entityAttributeValues[(int)$object->getId()][(int)$productStoreId])
            ) {
                $productStoreId = 0;
            }

            if (!isset($this->_entityAttributeValues[(int)$object->getId()][(int)$productStoreId])) {
                return false;
            }

            $attributeValue = $this->_entityAttributeValues[(int)$object->getId()][(int)$productStoreId];

            $attr = $object->getResource()->getAttribute($attrCode);
            if ($attr && $attr->getBackendType() === 'datetime') {
                $attributeValue = (int)\M2E\TikTokShop\Helper\Date::createDateGmt($attributeValue)->format('U');

                if (!is_int($this->getValueParsed())) {
                    $this->setValueParsed(
                        (int)\M2E\TikTokShop\Helper\Date::createDateGmt($this->getValue())->format('U')
                    );
                }
            } elseif ($attr && $attr->getFrontendInput() === 'multiselect') {
                $attributeValue = strlen((string)$attributeValue) ? explode(',', $attributeValue) : [];
            }

            return (bool)$this->validateAttribute($attributeValue);
        }
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    public function getValueElementRenderer()
    {
        if (strpos($this->getValueElementType(), '/') !== false) {
            return $this->_layout->getBlockSingleton($this->getValueElementType());
        }

        return $this->_layout->getBlockSingleton(
            \M2E\TikTokShop\Block\Adminhtml\Magento\Product\Rule\Renderer\Editable::class
        );
    }

    /**
     * Retrieve value element chooser URL
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $attribute = $this->getAttribute();
        if ($attribute !== 'sku' && $attribute !== 'category_ids') {
            return '';
        }

        $urlParameters = [
            'attribute' => $attribute,
            'store' => $this->getStoreId(),
            'form' => $this->getJsFormObject(),
        ];

        return $this->url->getUrl('*/general/getRuleConditionChooserHtml', $urlParameters);
    }

    /**
     * Customize default operator input by type mapper for some types
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            parent::getDefaultOperatorInputByType();
            /*
             * '{}' and '!{}' are left for back-compatibility and equal to '==' and '!='
             */
            $this->_defaultOperatorInputByType[AbstractModel::INPUT_TYPE_CATEGORY] = [
                '==',
                '!=',
                '{}',
                '!{}',
                '()',
                '!()',
            ];
            $this->_arrayInputTypes[] = AbstractModel::INPUT_TYPE_CATEGORY;
            /*
             * price and price range modification
             */
            $this->_defaultOperatorInputByType[AbstractModel::INPUT_TYPE_PRICE] = [
                '==',
                '!=',
                '>=',
                '>',
                '<=',
                '<',
                '{}',
                '!{}',
            ];
        }

        return $this->_defaultOperatorInputByType;
    }

    /**
     * Retrieve attribute object
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getAttributeObject()
    {
        try {
            $obj = $this->config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $this->getAttribute());
        } catch (\Exception $e) {
            $obj = new \Magento\Framework\DataObject();
            $obj->setEntity($this->productFactory->create())
                ->setFrontendInput('text');
        }

        return $obj;
    }

    /**
     * Add special attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        $attributes['attribute_set_id'] = __('Attribute Set');
        $attributes['category_ids'] = __('Category');

        foreach ($this->getCustomFilters() as $customFilterClassName) {
            // $this->_data property is not initialized jet, so we can't cache a created custom filter as
            // it requires that data
            $customFilterInstance = $this->getCustomFilterInstance($customFilterClassName, false);

            if ($customFilterInstance instanceof \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter) {
                $attributes[$customFilterClassName] = $customFilterInstance->getLabel();
            }
        }
    }

    /**
     * Load attribute options
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->magentoAttributeHelper->getAllAsObjects();

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            if (!$attribute->isAllowedForRuleCondition() || !$this->isAllowedForRuleCondition($attribute)) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);
        natcasesort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     *
     * @return bool
     */
    protected function isAllowedForRuleCondition($attribute)
    {
        return !in_array($attribute->getAttributeCode(), ['price_type', 'sku_type', 'weight_type']);
    }

    /**
     * Prepares values options to be used as select options or hashed array
     * Result is stored in following keys:
     *  'value_select_options' - normal select array: array(array('value' => $value, 'label' => $label), ...)
     *  'value_option' - hashed array: array($value => $label, ...),
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }

        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        if ($this->getAttribute() === 'attribute_set_id') {
            $entityTypeId = $this->config->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
            $selectOptions = $this->attrSetCollection
                ->setEntityTypeFilter($entityTypeId)
                ->load()
                ->toOptionArray();
        } elseif ($this->isFilterCustom($this->getAttribute())) {
            $selectOptions = $this->getCustomFilterInstance($this->getAttribute())->getOptions();
        } elseif (is_object($this->getAttributeObject())) {
            $attributeObject = $this->getAttributeObject();
            if ($attributeObject->usesSource()) {
                if ($attributeObject->getFrontendInput() === 'multiselect') {
                    $addEmptyOption = false;
                } else {
                    $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
            }
        }

        // Set new values only if we really got them
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $o) {
                    if (is_array($o['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$o['value']] = $o['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }

        return $this;
    }

    /**
     * Retrieve value by option
     *
     * @param mixed $option
     *
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();

        return $this->getData('value_option' . ($option !== null ? '/' . $option : ''));
    }

    /**
     * Retrieve select option values
     * @return array
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();

        return $this->getData('value_select_options');
    }

    /**
     * Retrieve after element HTML
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $image = $this->_assetRepo->getUrl('M2E_TikTokShop::images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' . $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __('Open Chooser') . '" /></a>';
        }

        return $html;
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     *
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        if ($attribute === 'category_ids' || $this->isFilterCustom($attribute)) {
            return $this;
        }

        if ($this->getAttributeObject()->isScopeGlobal()) {
            $attributes = $this->getRule()->getCollectedAttributes();
            $attributes[$attribute] = true;
            $this->getRule()->setCollectedAttributes($attributes);
            $productCollection->addAttributeToSelect($attribute, 'left');
        } else {
            $this->_entityAttributeValues = $productCollection->getAllAttributeValues($attribute);
        }

        return $this;
    }

    /**
     * Retrieve input type
     */
    public function getInputType(): string
    {
        if ($this->isFilterCustom($this->getAttribute())) {
            return $this->getCustomFilterInstance($this->getAttribute())
                        ->getInputType();
        }

        if ($this->getAttribute() === 'attribute_set_id') {
            return AbstractModel::INPUT_TYPE_SELECT;
        }

        if (!is_object($this->getAttributeObject())) {
            return AbstractModel::INPUT_TYPE_STRING;
        }

        if ($this->getAttributeObject()->getAttributeCode() === 'category_ids') {
            return AbstractModel::INPUT_TYPE_CATEGORY;
        }

        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
                return AbstractModel::INPUT_TYPE_SELECT;

            case 'multiselect':
                return AbstractModel::INPUT_TYPE_MULTISELECT;

            case 'date':
                return AbstractModel::INPUT_TYPE_DATE;

            case 'boolean':
                return AbstractModel::INPUT_TYPE_BOOLEAN;

            default:
                return AbstractModel::INPUT_TYPE_STRING;
        }
    }

    /**
     * Retrieve value element type
     * @return string
     */
    public function getValueElementType(): string
    {
        if ($this->isFilterCustom($this->getAttribute())) {
            return $this->getCustomFilterInstance($this->getAttribute())->getValueElementType();
        }
        if ($this->getAttribute() === 'attribute_set_id') {
            return AbstractModel::VALUE_ELEMENT_TYPE_SELECT;
        }
        if (!is_object($this->getAttributeObject())) {
            return AbstractModel::VALUE_ELEMENT_TYPE_TEXT;
        }
        switch ($this->getAttributeObject()->getFrontendInput()) {
            case 'select':
            case 'boolean':
                return AbstractModel::VALUE_ELEMENT_TYPE_SELECT;

            case 'multiselect':
                return AbstractModel::VALUE_ELEMENT_TYPE_MULTISELECT;

            case 'date':
                return AbstractModel::VALUE_ELEMENT_TYPE_DATE;

            default:
                return AbstractModel::VALUE_ELEMENT_TYPE_TEXT;
        }
    }

    /**
     * Retrieve value element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getValueElement(): \Magento\Framework\Data\Form\Element\AbstractElement
    {
        $element = parent::getValueElement();

        if (
            $this->isFilterCustom($this->getAttribute())
            && $this->getCustomFilterInstance($this->getAttribute())->getInputType() === AbstractModel::INPUT_TYPE_DATE
        ) {
            $element->setImage($this->_assetRepo->getUrl('M2E_TikTokShop::images/grid-cal.gif'));
        }

        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    $element->setImage($this->_assetRepo->getUrl('M2E_TikTokShop::images/grid-cal.gif'));
                    break;
            }
        }

        return $element;
    }

    /**
     * Retrieve Explicit Apply
     * @return bool
     */
    public function getExplicitApply(): bool
    {
        if (
            $this->isFilterCustom($this->getAttribute())
            && $this->getCustomFilterInstance($this->getAttribute())->getInputType() === AbstractModel::INPUT_TYPE_DATE
        ) {
            return true;
        }

        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                return true;
        }

        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
            }
        }

        return false;
    }

    /**
     * Load array
     *
     * @param array $arr
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Product
     */
    public function loadArray($arr)
    {
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();

        $isContainsOperator = !empty($arr['operator']) && in_array($arr['operator'], ['{}', '!{}']);
        if ($attribute && $attribute->getBackendType() === 'decimal' && !$isContainsOperator) {
            if (isset($arr['value'])) {
                if (
                    !empty($arr['operator'])
                    && in_array($arr['operator'], ['!()', '()'])
                    && false !== strpos($arr['value'], ',')
                ) {
                    $tmp = [];
                    foreach (explode(',', $arr['value']) as $value) {
                        $tmp[] = $this->localeFormat->getNumber($value);
                    }
                    $arr['value'] = implode(',', $tmp);
                } else {
                    $arr['value'] = $this->localeFormat->getNumber($arr['value']);
                }
            } else {
                $arr['value'] = false;
            }
            $arr['is_value_parsed'] = isset($arr['is_value_parsed'])
                ? $this->localeFormat->getNumber($arr['is_value_parsed']) : false;
        }

        return parent::loadArray($arr);
    }

    /**
     * Correct '==' and '!=' operators
     * Categories can't be equal because product is included categories selected by administrator and in their parents
     * @return string
     */
    public function getOperatorForValidate()
    {
        $op = $this->getOperator();
        if ($this->getInputType() === AbstractModel::INPUT_TYPE_CATEGORY) {
            if ($op === '==') {
                $op = '{}';
            } elseif ($op === '!=') {
                $op = '!{}';
            }
        }

        return $op;
    }

    protected function getCustomFilters(): array
    {
        return [
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\Magento\Stock::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\Magento\Qty::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\Magento\TypeId::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop\ProductId::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop\OnlineTitle::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop\OnlineSku::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop\OnlineQty::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop\OnlineCategory::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop\OnlinePrice::NICK,
            \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop\Status::NICK,
        ];
    }

    protected function isFilterCustom($filterId): bool
    {
        $customFilters = $this->getCustomFilters();

        return in_array($filterId, $customFilters, true);
    }

    /**
     * @return \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    protected function getCustomFilterInstance(
        string $filterType,
        bool $isReadyToCache = true
    ): ?\M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter {
        if (!$this->isFilterCustom($filterType)) {
            return null;
        }

        if (isset($this->_customFiltersCache[$filterType])) {
            return $this->_customFiltersCache[$filterType];
        }

        $model = $this->customFilterFactory->createByType($filterType);

        if ($isReadyToCache) {
            $this->_customFiltersCache[$filterType] = $model;
        }

        return $model;
    }

    private function getAttribute()
    {
        return $this->getData('attribute');
    }
}
