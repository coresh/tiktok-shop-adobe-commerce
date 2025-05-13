<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Settings\Tabs;

use M2E\TikTokShop\Helper\Component\TikTokShop\Configuration as ConfigurationHelper;

class Main extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;
    private ConfigurationHelper $configuration;

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        ConfigurationHelper $configuration,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->configuration = $configuration;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $configurationHelper = $this->configuration;

        $textAttributes = $this->magentoAttributeHelper->filterByInputTypes(
            $this->magentoAttributeHelper->getAll(),
            ['text', 'select', 'weight']
        );

        //region Product Identifier
        $fieldset = $form->addFieldset(
            'product_settings_fieldset',
            [
                'legend' => __('Product'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'identifier_code_custom_attribute',
            'hidden',
            [
                'name' => 'identifier_code_custom_attribute',
                'value' => $configurationHelper->getIdentifierCodeCustomAttribute(),
            ]
        );

        $preparedAttributes = [];

        $warningToolTip = '';

        if (
            $configurationHelper->isIdentifierCodeModeCustomAttribute() &&
            !$this->magentoAttributeHelper->isExistInAttributesArray(
                $configurationHelper->getIdentifierCodeCustomAttribute(),
                $textAttributes
            ) && $this->getData('identifier_code_custom_attribute') != ''
        ) {
            $warningText = __('Selected Magento Attribute is invalid. Please ensure that the Attribute ' .
                'exists in your Magento, has a relevant Input Type and it is included in all Attribute Sets. ' .
                'Otherwise, select a different Attribute from the drop-down.');

            $warningToolTip = __(
                <<<HTML
<span class="fix-magento-tooltip m2e-tooltip-grid-warning">
    {$this->getTooltipHtml((string)$warningText)}
</span>
HTML
            );

            $attrs = ['attribute_code' => $configurationHelper->getIdentifierCodeCustomAttribute()];
            $attrs['selected'] = 'selected';
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => ConfigurationHelper::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $this->magentoAttributeHelper
                    ->getAttributeLabel($configurationHelper->getIdentifierCodeCustomAttribute()),
            ];
        }

        foreach ($textAttributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];

            if (
                $configurationHelper->isIdentifierCodeModeCustomAttribute() &&
                $attribute['code'] == $configurationHelper->getIdentifierCodeCustomAttribute()
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => ConfigurationHelper::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'identifier_code_mode',
            self::SELECT,
            [
                'name' => 'identifier_code_mode',
                'label' => __('EAN / UPC'),
                'class' => 'tiktokshop-identifier-code',
                'values' => [
                    ConfigurationHelper::IDENTIFIER_CODE_MODE_NOT_SET => __('Not Set'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => !$configurationHelper->isIdentifierCodeModeCustomAttribute()
                    ? $configurationHelper->getIdentifierCodeMode()
                    : '',
                'create_magento_attribute' => true,
                'tooltip' => __(
                    '%channel_title uses EAN/UPC to associate your Item with its catalog. ' .
                    'Select Attribute where the Product ID values are stored.',
                    [
                        'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
                    ]
                ),
                'after_element_html' => $warningToolTip,
                'required' => false,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text');
        //endregion

        $fieldset = $form->addFieldset(
            'package_settings_fieldset',
            [
                'legend' => __('Package'),
                'collapsable' => false,
            ]
        );

        $this->addPackageDimensionField(ConfigurationHelper::DIMENSION_TYPE_WEIGHT, $fieldset, $textAttributes);
        $this->addPackageDimensionField(ConfigurationHelper::DIMENSION_TYPE_LENGTH, $fieldset, $textAttributes);
        $this->addPackageDimensionField(ConfigurationHelper::DIMENSION_TYPE_WIDTH, $fieldset, $textAttributes);
        $this->addPackageDimensionField(ConfigurationHelper::DIMENSION_TYPE_HEIGHT, $fieldset, $textAttributes);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->jsTranslator->add(
            'value_must_be_greater_than_zero_error',
            __('Enter a numeric value greater than zero')
        );

        $this->jsTranslator->add(
            'not_valid_weight_message',
            __("The package weight must be a positive number")
        );
        $this->jsTranslator->add(
            'not_valid_length_message',
            __("The package length needs to be a whole number that's not negative")
        );
        $this->jsTranslator->add(
            'not_valid_width_message',
            __("The package width needs to be a whole number that's not negative")
        );
        $this->jsTranslator->add(
            'not_valid_height_message',
            __("The package height needs to be a whole number that's not negative")
        );

        $this->jsUrl->add(
            $this->getUrl('*/settings/save'),
            \M2E\TikTokShop\Block\Adminhtml\Settings\Tabs::TAB_ID_MAIN
        );

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(
                ConfigurationHelper::class
            )
        );

        $this->js->add(
            <<<JS
require([
    'TikTokShop/Settings/Main'
], function(){
    window.TikTokShopSettingsMainObj = new TikTokShopSettingsMain();
});
JS
        );

        return parent::_beforeToHtml();
    }

    private function addPackageDimensionField(
        string $type,
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        array $textAttributes
    ): void {
        $mode = $this->configuration->getPackageDimensionMode($type);

        $this->addHiddenInputForCustomAttributeValue($fieldset, $type);
        [$preparedAttributes, $warningToolTip] = $this->prepareAttributesAndWarningTooltip($type, $textAttributes);

        $fieldset->addField(
            "package_{$type}_mode",
            self::SELECT,
            [
                'name' => "package_{$type}_mode",
                'label' => $this->getPackageDimensionLabel($type),
                'class' => "validator-required-when-visible",
                'values' => [
                    ConfigurationHelper::PACKAGE_MODE_NOT_SET => __('Not Set'),
                    ConfigurationHelper::PACKAGE_MODE_CUSTOM_VALUE => __('Custom Value'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => $mode !== ConfigurationHelper::PACKAGE_MODE_CUSTOM_ATTRIBUTE ? $mode : '',
                'create_magento_attribute' => true,
                'tooltip' => $this->getPackageDimensionTooltipText($type),
                'after_element_html' => $this->getCustomValueInputHtml($type) . $warningToolTip,
                'required' => true,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text');
    }

    private function addHiddenInputForCustomAttributeValue(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        string $type
    ): void {
        $fieldset->addField(
            "package_{$type}_custom_attribute",
            'hidden',
            [
                'name' => "package_{$type}_custom_attribute",
                'value' => $this->configuration->getPackageDimensionCustomAttribute($type),
            ]
        );
    }

    private function prepareAttributesAndWarningTooltip(string $type, array $textAttributes): array
    {
        $mode = $this->configuration->getPackageDimensionMode($type);
        $customAttribute = $this->configuration->getPackageDimensionCustomAttribute($type);

        $preparedAttributes = [];
        $warningToolTip = '';
        if (
            $mode === ConfigurationHelper::PACKAGE_MODE_CUSTOM_ATTRIBUTE
            && !$this->magentoAttributeHelper->isExistInAttributesArray(
                $customAttribute,
                $textAttributes
            )
            && $this->getData("package_{$type}_custom_attribute") != ''
        ) {
            $warningText = __("Selected Magento Attribute is invalid. Please ensure that the " .
                "Attribute exists in your Magento, has a relevant Input Type and it is included in all Attribute Sets. " .
                "Otherwise, select a different Attribute from the drop-down.");

            $warningToolTip = __(
                <<<HTML
<span class="fix-magento-tooltip m2e-tooltip-grid-warning">
    {$this->getTooltipHtml((string)$warningText)}
</span>
HTML
            );

            $attrs = ['attribute_code' => $customAttribute];
            $attrs['selected'] = 'selected';
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => ConfigurationHelper::PACKAGE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $this->magentoAttributeHelper
                    ->getAttributeLabel($customAttribute),
            ];
        }

        foreach ($textAttributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];

            if (
                $mode === ConfigurationHelper::PACKAGE_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $customAttribute
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => ConfigurationHelper::PACKAGE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        return [$preparedAttributes, $warningToolTip];
    }

    private function getCustomValueInputHtml(string $type): string
    {
        $customValue = $this->configuration->getPackageDimensionCustomValue($type);

        $classes = [
            'TikTokShop-required-when-visible',
            'admin__control-text',
            'validator-greater-than-zero',
            'validator-tts-' . $type,
        ];

        $attributes = [
            'type' => 'text',
            'class' => implode(' ', $classes),
            'style' => 'max-width: 150px;',
            'id' => "package_{$type}_custom_value",
            'name' => "package_{$type}_custom_value",
            'value' => !empty($customValue) ? $customValue : null,
            'placeholder' => __('Enter value here'),
        ];

        if (
            $this->configuration->getPackageDimensionMode($type)
            !== ConfigurationHelper::PACKAGE_MODE_CUSTOM_VALUE
        ) {
            $attributes['style'] .= 'display:none';
        }

        return '<input ' . $this->renderHtmlAttributes($attributes) . '>';
    }

    private function renderHtmlAttributes(array $attributes): string
    {
        $preparedAttributes = [];
        foreach ($attributes as $attributeName => $attributeValue) {
            $preparedAttributes[] = sprintf(
                '%s="%s"',
                $attributeName,
                $this->_escaper->escapeHtml($attributeValue)
            );
        }

        return implode(' ', $preparedAttributes);
    }

    private function getPackageDimensionLabel(string $type): string
    {
        $labels = [
            ConfigurationHelper::DIMENSION_TYPE_WEIGHT => __('Weight'),
            ConfigurationHelper::DIMENSION_TYPE_LENGTH => __('Length'),
            ConfigurationHelper::DIMENSION_TYPE_WIDTH => __('Width'),
            ConfigurationHelper::DIMENSION_TYPE_HEIGHT => __('Height'),
        ];

        return (string)($labels[$type] ?? 'N/A');
    }

    private function getPackageDimensionTooltipText(string $type): string
    {
        switch ($type) {
            case ConfigurationHelper::DIMENSION_TYPE_WEIGHT:
                return (string)__("The package weight must be a positive number. ( kg (UK), lb (US))");
            case ConfigurationHelper::DIMENSION_TYPE_LENGTH:
                return (string)__("The package length needs to be a whole number that's not negative. Possible Units: US: CENTIMETER, INCH, Other  regions: CENTIMETER");
            case ConfigurationHelper::DIMENSION_TYPE_WIDTH:
                return (string)__("The package width needs to be a whole number that's not negative. Possible Units: US: CENTIMETER, INCH, Other regions: CENTIMETER");
            case ConfigurationHelper::DIMENSION_TYPE_HEIGHT:
                return (string)__("The package height needs to be a whole number that's not negative. Possible Units: US: CENTIMETER, INCH, Other regions: CENTIMETER");
        }

        return 'N/A';
    }
}
