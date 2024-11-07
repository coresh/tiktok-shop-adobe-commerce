<?php

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Condition;

class Combine extends AbstractModel
{
    protected $_logger;
    protected $_useCustomOptions = true;

    protected static $_conditionModels = [];

    private \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\ProductFactory $ruleConditionProductFactory;
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\ProductFactory $ruleConditionProductFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_logger = $context->getLogger();

        $this->setType(self::class)
             ->setAggregator('all')
             ->setValue(true)
             ->setConditions([])
             ->setActions([]);

        $this->loadAggregatorOptions();
        if ($options = $this->getAggregatorOptions()) {
            foreach ($options as $aggregator => $dummy) {
                $this->setAggregator($aggregator);
                break;
            }
        }
        $this->ruleConditionProductFactory = $ruleConditionProductFactory;
        $this->objectManager = $objectManager;
    }

    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        foreach ($this->getConditions() as $condition) {
            $condition->setStoreId($storeId);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions(): array
    {
        $conditions = [
            [
                'label' => (string)__('Conditions Combination'),
                'value' => $this->getConditionCombine(),
            ],
        ];

        $customAttribute = $this->getCustomOptionsAttributes();
        if ($this->_useCustomOptions && !empty($customAttribute)) {
            $conditions[] = [
                'label' => $this->getCustomLabel(),
                'value' => $this->getCustomOptions(),
            ];
        }

        $conditions[] = [
            'label' => (string)__('Product Attribute'),
            'value' => $this->getProductOptions(),
        ];

        return array_merge_recursive(parent::getNewChildSelectOptions(), $conditions);
    }

    protected function getConditionCombine()
    {
        return $this->getType();
    }

    // ---------------------------------------

    protected function getCustomLabel()
    {
        return '';
    }

    protected function getCustomOptions()
    {
        return [];
    }

    protected function getCustomOptionsAttributes()
    {
        return [];
    }

    // ---------------------------------------

    protected function getProductOptions()
    {
        $attributes = $this->ruleConditionProductFactory->create()->getAttributeOption();

        if (empty($attributes)) {
            return [];
        }

        return $this->getOptions(
            \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\Product::class,
            $attributes
        );
    }

    // ---------------------------------------

    protected function getOptions($value, array $optionsAttribute, array $params = [])
    {
        $options = [];
        $suffix = (count($params)) ? '|' . implode('|', $params) . '|' : '|';
        foreach ($optionsAttribute as $code => $label) {
            $options[] = [
                'value' => $value . $suffix . $code,
                'label' => $label,
            ];
        }

        return $options;
    }

    // ---------------------------------------

    public function setCustomOptionsFlag($flag)
    {
        $this->_useCustomOptions = (bool)$flag;

        return $this;
    }

    //########################################

    public function loadAggregatorOptions()
    {
        $this->setAggregatorOption([
            'all' => __('ALL'),
            'any' => __('ANY'),
        ]);

        return $this;
    }

    public function getAggregatorSelectOptions()
    {
        $opt = [];
        foreach ($this->getAggregatorOption() as $k => $v) {
            $opt[] = ['value' => $k, 'label' => $v];
        }

        return $opt;
    }

    public function getAggregatorName()
    {
        return $this->getAggregatorOption($this->getAggregator());
    }

    public function getAggregatorElement()
    {
        if ($this->getAggregator() === null) {
            foreach ($this->getAggregatorOption() as $k => $v) {
                $this->setAggregator($k);
                break;
            }
        }

        return $this->getForm()->addField($this->getPrefix() . '__' . $this->getId() . '__aggregator', 'select', [
            'name' => 'rule[' . $this->getPrefix() . '][' . $this->getId() . '][aggregator]',
            'values' => $this->getAggregatorSelectOptions(),
            'value' => $this->getAggregator(),
            'value_name' => $this->getAggregatorName(),
        ])->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    //########################################

    public function loadValueOptions()
    {
        $this->setValueOption([
            1 => __('TRUE'),
            0 => __('FALSE'),
        ]);

        return $this;
    }

    public function addCondition($condition)
    {
        $condition->setRule($this->getRule());
        $condition->setObject($this->getObject());
        $condition->setPrefix($this->getPrefix());

        $conditions = $this->getConditions();
        $conditions[] = $condition;

        if (!$condition->getId()) {
            $condition->setId($this->getId() . '--' . count($conditions));
        }

        $this->setData($this->getPrefix(), $conditions);

        return $this;
    }

    public function getValueElementType(): string
    {
        return \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\AbstractModel::VALUE_ELEMENT_TYPE_TEXT;
    }

    //########################################

    protected function beforeLoadValidate($condition)
    {
        if (empty($condition['attribute'])) {
            return true;
        }

        if (
            !$this->_useCustomOptions &&
            array_key_exists($condition['attribute'], $this->getCustomOptionsAttributes())
        ) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAggregator(
            $arr['aggregator'] ?? ($arr['attribute'] ?? null)
        )
             ->setValue(
                 $arr['value'] ?? ($arr['operator'] ?? null)
             );

        if (!empty($arr[$key]) && is_array($arr[$key])) {
            foreach ($arr[$key] as $condArr) {
                try {
                    if (!$this->beforeLoadValidate($condArr)) {
                        continue;
                    }

                    $cond = $this->_getNewConditionModelInstance($condArr['type']);
                    if ($cond) {
                        if ($cond instanceof \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\Combine) {
                            $cond->setData($this->getPrefix(), []);
                            $cond->setCustomOptionsFlag($this->_useCustomOptions);
                        }

                        $this->addCondition($cond);
                        $cond->loadArray($condArr, $key);
                    }
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }

        return $this;
    }

    public function loadXml($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml);
        }
        $arr = parent::loadXml($xml);
        foreach ($xml->conditions->children() as $condition) {
            $arr['conditions'] = parent::loadXml($condition);
        }
        $this->loadArray($arr);

        return $this;
    }

    //########################################

    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = "<aggregator>" . $this->getAggregator() . "</aggregator>"
            . "<value>" . $this->getValue() . "</value>"
            . "<$containerKey>";
        foreach ($this->getConditions() as $condition) {
            $xml .= "<$itemKey>" . $condition->asXml() . "</$itemKey>";
        }
        $xml .= "</$containerKey>";

        return $xml;
    }

    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray();
        $out['aggregator'] = $this->getAggregator();

        foreach ($this->getConditions() as $condition) {
            $out['conditions'][] = $condition->asArray();
        }

        return $out;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            __(
                'If %1 of these Conditions are %2:',
                $this->getAggregatorElement()->getHtml(),
                $this->getValueElement()->getHtml(),
            );

        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }

        return $html;
    }

    public function asHtmlRecursive()
    {
        $html = $this->asHtml() .
            '<ul id="' . $this->getPrefix() . '__' . $this->getId() . '__children" class="rule-param-children">';
        foreach ($this->getConditions() as $cond) {
            $html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
        }
        $html .= '<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>';

        return $html;
    }

    public function asString($format = '')
    {
        $str = (string)__(
            "If %1 of these Conditions are %1:",
            $this->getAggregatorName(),
            $this->getValueName()
        );

        return $str;
    }

    public function asStringRecursive($level = 0)
    {
        $str = parent::asStringRecursive($level);
        foreach ($this->getConditions() as $cond) {
            $str .= "\n" . $cond->asStringRecursive($level + 1);
        }

        return $str;
    }

    //########################################

    public function getNewChildElement()
    {
        return $this->getForm()->addField($this->getPrefix() . '__' . $this->getId() . '__new_child', 'select', [
            'name' => 'rule[' . $this->getPrefix() . '][' . $this->getId() . '][new_child]',
            'values' => $this->getNewChildSelectOptions(),
            'value_name' => $this->getNewChildName(),
        ])->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Newchild::class)
        );
    }

    public function validate(\Magento\Framework\DataObject $object)
    {
        if (!$this->getConditions()) {
            return true;
        }

        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();

        foreach ($this->getConditions() as $cond) {
            $validated = $cond->validate($object);

            if ($all && $validated !== $true) {
                return false;
            } elseif (!$all && $validated === $true) {
                return true;
            }
        }

        return $all ? true : false;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }

    public function setJsFormObject($form)
    {
        $this->setData('js_form_object', $form);
        foreach ($this->getConditions() as $condition) {
            $condition->setJsFormObject($form);
        }

        return $this;
    }

    public function getConditions()
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'conditions';

        return (array)$this->getData($key);
    }

    public function setConditions($conditions)
    {
        $key = $this->getPrefix() ? $this->getPrefix() : 'conditions';

        return $this->setData($key, $conditions);
    }

    public function getConditionModels()
    {
        return self::$_conditionModels;
    }

    //########################################

    protected function _getRecursiveChildSelectOption()
    {
        return ['value' => $this->getType(), 'label' => __('Conditions Combination')];
    }

    protected function _getNewConditionModelInstance($modelClass)
    {
        if (empty($modelClass)) {
            return false;
        }

        if (!array_key_exists($modelClass, self::$_conditionModels)) {
            $model = $this->objectManager->create($modelClass);
            self::$_conditionModels[$modelClass] = $model;
        } else {
            $model = self::$_conditionModels[$modelClass];
        }

        if (!$model) {
            return false;
        }

        $newModel = clone $model;

        return $newModel;
    }
}
