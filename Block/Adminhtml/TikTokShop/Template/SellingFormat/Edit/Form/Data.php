<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\SellingFormat\Edit\Form;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;
use M2E\TikTokShop\Model\Template\SellingFormat;
use M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat as ResourceSellingFormat;

class Data extends AbstractForm
{
    private \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper;
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;
    /** @var \M2E\TikTokShop\Model\Template\SellingFormat\BuilderFactory */
    private SellingFormat\BuilderFactory $templateSellingFormatBuilderFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Template\SellingFormat\BuilderFactory $templateSellingFormatBuilderFactory,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->templateSellingFormatBuilderFactory = $templateSellingFormatBuilderFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): Data
    {
        $attributes = $this->globalDataHelper->getValue('tiktokshop_attributes');

        $attributesByInputTypes = $this->getAttributesByInputTypes();

        $formData = $this->getFormData();
        $default = $this->getDefault();
        $formData = array_merge($default, $formData);

        $formData['fixed_price_modifier'] =
            \M2E\Core\Helper\Json::decode($formData['fixed_price_modifier']) ?: [];

        $form = $this->_formFactory->create();
        $channelTitle = \M2E\TikTokShop\Helper\Module::getChannelTitle();

        $form->addField(
            'selling_format_id',
            'hidden',
            [
                'name' => 'selling_format[id]',
                'value' => $formData['id'] ?? '',
            ]
        );

        $form->addField(
            'selling_format_title',
            'hidden',
            [
                'name' => 'selling_format[title]',
                'value' => $this->getTitle(),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_selling_format_edit_form_qty_and_duration',
            [
                'legend' => __('Quantity'),
                'collapsable' => true,
            ]
        );

        $preparedAttributes = [
            [
                'value' => SellingFormat::QTY_MODE_PRODUCT_FIXED,
                'label' => __('QTY'),
            ],
        ];

        if (
            $formData['qty_mode'] == SellingFormat::QTY_MODE_ATTRIBUTE
            && !$this->magentoAttributeHelper
                ->isExistInAttributesArray($formData['qty_custom_attribute'], $attributes)
            && $formData['qty_custom_attribute'] != ''
        ) {
            $preparedAttributes[] = [
                'attrs' => [
                    'attribute_code' => $formData['qty_custom_attribute'],
                    'selected' => 'selected',
                ],
                'value' => SellingFormat::QTY_MODE_ATTRIBUTE,
                'label' => $this->magentoAttributeHelper->getAttributeLabel($formData['qty_custom_attribute']),
            ];
        }

        foreach ($attributesByInputTypes['text'] as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];
            if (
                $formData['qty_mode'] == SellingFormat::QTY_MODE_ATTRIBUTE
                && $formData['qty_custom_attribute'] == $attribute['code']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => SellingFormat::QTY_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'qty_mode',
            self::SELECT,
            [
                'container_id' => 'qty_mode_tr',
                'name' => 'selling_format[qty_mode]',
                'label' => __('Quantity'),
                'values' => [
                    SellingFormat::QTY_MODE_PRODUCT => __('Product Quantity'),
                    SellingFormat::QTY_MODE_NUMBER => __('Custom Value'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                            'new_option_value' => SellingFormat::QTY_MODE_ATTRIBUTE,
                        ],
                    ],
                ],
                'value' => $formData['qty_mode'] != \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE
                    ? $formData['qty_mode'] : '',
                'create_magento_attribute' => true,
                'tooltip' => __(
                    'The number of Items you want to sell on %channel_title.<br/><br/>' .
                    '<b>Product Quantity:</b> the number of Items on %channel_title will be the same as in Magento.<br/>' .
                    '<b>Custom Value:</b> set a Quantity in the Policy here.<br/>' .
                    '<b>Magento Attribute:</b> takes the number from the Attribute you specify.',
                    [
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle(),
                        'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
                    ]
                ),
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text');

        $fieldset->addField(
            'qty_custom_attribute',
            'hidden',
            [
                'name' => 'selling_format[qty_custom_attribute]',
            ]
        );

        $fieldset->addField(
            'qty_custom_value',
            'text',
            [
                'container_id' => 'qty_mode_cv_tr',
                'label' => __('Quantity Value'),
                'name' => 'selling_format[qty_custom_value]',
                'value' => $formData['qty_custom_value'],
                'class' => 'validate-digits',
                'required' => true,
            ]
        );

        $preparedAttributes = [];
        for ($i = 100; $i >= 5; $i -= 5) {
            $preparedAttributes[] = [
                'value' => $i,
                'label' => $i . ' %',
            ];
        }

        $fieldset->addField(
            'qty_percentage',
            self::SELECT,
            [
                'container_id' => 'qty_percentage_tr',
                'label' => __('Quantity Percentage'),
                'name' => 'selling_format[qty_percentage]',
                'values' => $preparedAttributes,
                'value' => $formData['qty_percentage'],
                'tooltip' => __(
                    'Sets the percentage for calculation of Items number to be Listed ' .
                    'on %channel_title basing on Product Quantity or Magento Attribute. E.g., if Quantity Percentage ' .
                    'is set to 10% and Product Quantity is 100, the Quantity to be Listed on %channel_title will ' .
                    'be calculated as <br/>100 *10% = 10.<br/>',
                    [
                        'channel_title' => $channelTitle
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'qty_modification_mode',
            self::SELECT,
            [
                'container_id' => 'qty_modification_mode_tr',
                'label' => __('Conditional Quantity'),
                'name' => 'selling_format[qty_modification_mode]',
                'values' => [
                    SellingFormat::QTY_MODIFICATION_MODE_OFF => __('Disabled'),
                    SellingFormat::QTY_MODIFICATION_MODE_ON => __('Enabled'),
                ],
                'value' => $formData['qty_modification_mode'],
                'tooltip' => __(
                    'Choose whether to limit the amount of Stock you list on %channel_title, eg ' .
                    'because you want to set some Stock aside for sales off %channel_title.<br/><br/>If this Setting ' .
                    'is <b>Enabled</b> you can specify the maximum Quantity to be Listed. If this Setting ' .
                    'is <b>Disabled</b> all Stock for the Product will be Listed as available on %channel_title.',
                    [
                        'channel_title' => $channelTitle
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'qty_min_posted_value',
            'text',
            [
                'container_id' => 'qty_min_posted_value_tr',
                'label' => __('Minimum Quantity to Be Listed'),
                'name' => 'selling_format[qty_min_posted_value]',
                'value' => $formData['qty_min_posted_value'],
                'class' => 'TikTokShop-validate-qty',
                'required' => true,
                'tooltip' => __(
                    'If you have 2 pieces in Stock but set a Minimum Quantity to Be Listed of 5, ' .
                    'Item will not be Listed on %channel_title.<br/> Otherwise, the Item will be Listed with ' .
                    'Quantity according to the Settings in the Selling Policy',
                    [
                        'channel_title' => $channelTitle
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'qty_max_posted_value',
            'text',
            [
                'container_id' => 'qty_max_posted_value_tr',
                'label' => __('Maximum Quantity to Be Listed'),
                'name' => 'selling_format[qty_max_posted_value]',
                'value' => $formData['qty_max_posted_value'],
                'class' => 'TikTokShop-validate-qty',
                'required' => true,
                'tooltip' => __(
                    'Set a maximum number to sell on %channel_title, e.g. if you ' .
                    'have 10 Items in Stock but want to keep 2 Items back, set a Maximum Quantity of 8.',
                    [
                        'channel_title' => $channelTitle
                    ]
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_selling_format_edit_form_qty_and_delivery',
            [
                'legend' => __('Delivery'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'cash_on_delivery',
            self::SELECT,
            [
                'label' => __('Cash on delivery'),
                'name' => 'selling_format[cash_on_delivery]',
                'values' => [
                    SellingFormat::CASH_ON_DELIVERY_OFF => __('Disabled'),
                    SellingFormat::CASH_ON_DELIVERY_ON => __('Enabled'),
                ],
                'value' => $formData['cash_on_delivery'],
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_selling_format_edit_form_prices',
            [
                'legend' => __('Price'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE,
            self::SELECT,
            [
                'name' => 'selling_format[' . ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE . ']',
                'label' => __('Non-Sellable Gift'),
                'values' => [
                    SellingFormat::IS_NOT_FOR_SALE_OFF => __('No'),
                    SellingFormat::IS_NOT_FOR_SALE_ON => __('Yes'),
                ],
                'value' => $formData[ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE],
                'tooltip' => __(
                    'Enable this option to list a non-sellable product as a Gift with Purchase. It won’t ' .
                    'appear in %channel_title searches or recommendations, and its price will be shown as $0.',
                    [
                        'channel_title' => $channelTitle
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'price_table_container',
            self::CUSTOM_CONTAINER,
            [
                'text' => $this->getPriceTableHtml(),
                'css_class' => 'TikTokShop-fieldset-table',
            ]
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Element\Dependence::class)
                 ->addFieldMap(
                     ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE,
                     ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE
                 )
                 ->addFieldMap('price_table_container', 'price_table_container')
                 ->addFieldDependence('price_table_container', ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE, 0)
        );

        $this->setForm($form);

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(SellingFormat::class)
        );
        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Model\TikTokShop\Template\Manager::class)
        );

        $this->jsTranslator->addTranslations([
            'wrong_value_more_than_30' => __('Wrong value. Must be no more than 30. Max applicable ' .
                'length is 6 characters, including the decimal (e.g., 12.345).'),
            'Price Change is not valid.' => __('Price Change is not valid.'),
            'Wrong value. Only integer numbers.' => __('Wrong value. Only integer numbers.'),
            'Price' => __('Price'),
            '% of Price' => __('% of Price'),
        ]);

        $jsMode = \M2E\Core\Helper\Data::escapeJs((string)$formData['qty_mode']);
        $jsQty = \M2E\Core\Helper\Data::escapeJs((string)$formData['qty_modification_mode']);

        $this->js->add("TikTokShop.formData.qty_mode = $jsMode;");
        $this->js->add("TikTokShop.formData.qty_modification_mode = $jsQty;");

        $fixedPriceModifierRenderJs = '';
        if (!empty($formData['fixed_price_modifier'])) {
            $formDataJson = \M2E\Core\Helper\Json::encode($formData['fixed_price_modifier']);
            $fixedPriceModifierRenderJs = <<<JS
    TikTokShopTemplateSellingFormatObj.renderFixedPriceChangeRows({$formDataJson});
JS;
        }

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Template/SellingFormat',
    ], function(){
        window.TikTokShopTemplateSellingFormatObj = new TikTokShopTemplateSellingFormat();
        TikTokShopTemplateSellingFormatObj.initObservers();

       {$fixedPriceModifierRenderJs}
    });
JS
        );

        return parent::_prepareForm();
    }

    private function getTitle()
    {
        $template = $this->globalDataHelper->getValue('tiktokshop_template_selling_format');

        if ($template === null) {
            return '';
        }

        return $template->getTitle();
    }

    private function getFormData()
    {
        $template = $this->globalDataHelper->getValue('tiktokshop_template_selling_format');

        if ($template === null || $template->getId() === null) {
            return [];
        }

        return $template->getData();
    }

    private function getAttributesByInputTypes()
    {
        $attributes = $this->globalDataHelper->getValue('tiktokshop_attributes');

        return [
            'text' => $this->magentoAttributeHelper->filterByInputTypes($attributes, ['text']),
            'text_select' => $this->magentoAttributeHelper->filterByInputTypes($attributes, ['text', 'select']),
            'text_price' => $this->magentoAttributeHelper->filterByInputTypes($attributes, ['text', 'price']),
        ];
    }

    private function getDefault(): array
    {
        return $this->templateSellingFormatBuilderFactory->create()->getDefaultData();
    }

    private function getPriceTableHtml(): string
    {
        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\SellingFormat\Edit\Form\PriceTable::class
        );
        $block->addData([
            'currency' => $this->getCurrency(),
            'form_data' => $this->getFormData(),
            'default' => $this->getDefault(),
            'attributes_by_input_types' => $this->getAttributesByInputTypes(),
        ]);

        return $block->toHtml();
    }
}
