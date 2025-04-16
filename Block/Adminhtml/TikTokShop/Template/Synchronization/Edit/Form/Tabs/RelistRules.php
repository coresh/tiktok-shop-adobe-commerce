<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Synchronization\Edit\Form\Tabs;

use M2E\TikTokShop\Model\Template\Synchronization as TemplateSynchronization;

class RelistRules extends AbstractTab
{
    /** @var \M2E\TikTokShop\Model\Template\Synchronization\Builder */
    private TemplateSynchronization\Builder $synchronizationBuilder;
    private \M2E\TikTokShop\Model\Magento\Product\RuleFactory $ruleFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Template\Synchronization\Builder $synchronizationBuilder,
        \M2E\TikTokShop\Model\Magento\Product\RuleFactory $ruleFactory,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->synchronizationBuilder = $synchronizationBuilder;
        $this->ruleFactory = $ruleFactory;
        parent::__construct(
            $globalDataHelper,
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    protected function _prepareForm()
    {
        $default = $this->synchronizationBuilder->getDefaultData();
        $formData = $this->getFormData();

        $formData = array_merge($default, $formData);

        $form = $this->_formFactory->create();

        $form->addField(
            'template_synchronization_form_data_relist',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '<p>If <strong>Relist Action</strong> is enabled, %extension_title ' .
                    'will relist Items that have been stopped or finished on %channel_title if they meet the ' .
                    'Conditions you set. (Relist Action will not list Items that have not been Listed yet)</p><br>' .
                    '<p>If the automatic relisting doesn\'t work (usually because of the errors returned ' .
                    'from %channel_title), %extension_title will attempt to list the Item again only if there is ' .
                    "a change of Product Status, Stock Availability or Quantity in Magento.</p><br>" .
                    '<p>More detailed information about how to work with this Page you can find ' .
                    '<a href="%url" target="_blank" class="external-link">here</a>.</p>',
                    [
                        'url' => 'https://docs-m2.m2epro.com/relist-rules-for-tiktok-shop-listings',
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle(),
                        'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
                    ],
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_synchronization_form_data_relist_filters',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'relist_mode',
            self::SELECT,
            [
                'name' => 'synchronization[relist_mode]',
                'label' => __('Relist Action'),
                'value' => $formData['relist_mode'],
                'values' => [
                    0 => __('Disabled'),
                    1 => __('Enabled'),
                ],
                'tooltip' => __(
                    'Choose whether you want to Relist Items covered by
                    %extension_title Listings using this Policy if the Relist Conditions are met.',
                    [
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'relist_filter_user_lock',
            self::SELECT,
            [
                'container_id' => 'relist_filter_user_lock_tr_container',
                'name' => 'synchronization[relist_filter_user_lock]',
                'label' => __('Relist When Stopped Manually'),
                'value' => $formData['relist_filter_user_lock'],
                'values' => [
                    1 => __('No'),
                    0 => __('Yes'),
                ],
                'tooltip' => __('Choose whether you want the Automatic Relist Rules to Relist Items ' .
                    'even if they\'ve been Stopped manually.'),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_synchronization_form_data_relist_rules',
            [
                'legend' => __('Relist Conditions'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'relist_status_enabled',
            self::SELECT,
            [
                'name' => 'synchronization[relist_status_enabled]',
                'label' => __('Product Status'),
                'value' => $formData['relist_status_enabled'],
                'values' => [
                    0 => __('Any'),
                    1 => __('Enabled'),
                ],
                'class' => 'TikTokShop-validate-stop-relist-conditions-product-status',
                'tooltip' => __(
                    '<p><strong>Enabled:</strong> Relist Items on %channel_title automatically
                    if they have status Enabled in Magento Product. (Recommended)</p>
                    <p><strong>Any:</strong> Relist Items on %channel_title automatically with any
                    Magento Product status.</p>',
                    [
                        'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
                    ]
                ),
            ]
        );

        $fieldset->addField(
            'relist_is_in_stock',
            self::SELECT,
            [
                'name' => 'synchronization[relist_is_in_stock]',
                'label' => __('Stock Availability'),
                'value' => $formData['relist_is_in_stock'],
                'values' => [
                    0 => __('Any'),
                    1 => __('In Stock'),
                ],
                'class' => 'TikTokShop-validate-stop-relist-conditions-stock-availability',
                'tooltip' => __('<p><strong>In Stock:</strong> Relist Items automatically ' .
                    'if Products are in Stock. (Recommended)</p>' .
                    '<p><strong>Any:</strong> Relist Items automatically regardless of Stock availability.</p>'),
            ]
        );

        $form->addField(
            'relist_qty_calculated_confirmation_popup_template',
            self::CUSTOM_CONTAINER,
            [
                'text' => __(
                    'Disabling this option might affect actual product data updates. ' .
                    'Please read <a href="%url" target="_blank">this article</a> before disabling the option.',
                    ['url' => 'https://help.m2epro.com/support/solutions/articles/9000199813'],
                ),
                'style' => 'display: none;',
            ]
        );

        $fieldset->addField(
            'relist_qty_calculated',
            self::SELECT,
            [
                'name' => 'synchronization[relist_qty_calculated]',
                'label' => __('Quantity'),
                'value' => $formData['relist_qty_calculated'],
                'values' => [
                    TemplateSynchronization::QTY_MODE_NONE => __('Any'),
                    TemplateSynchronization::QTY_MODE_YES => __('More or Equal'),
                ],
                'class' => 'TikTokShop-validate-stop-relist-conditions-item-qty',
                'tooltip' => __('<p><strong>Any:</strong> Relist Items automatically with ' .
                    'any Quantity available.</p><p><strong>More or Equal:</strong> ' .
                    'Relist Items automatically if the Quantity is at least equal to the number ' .
                    'you set, according to the Selling Policy. (Recommended)</p>'),
            ]
        )->setAfterElementHtml(
            <<<HTML
<input name="synchronization[relist_qty_calculated_value]" id="relist_qty_calculated_value"
       value="{$formData['relist_qty_calculated_value']}"
       type="text"
       style="width: 72px; margin-left: 10px;"
       class="input-text admin__control-text required-entry validate-digits _required" />
HTML
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_synchronization_relist_advanced_filters',
            [
                'legend' => __('Advanced Conditions'),
                'collapsable' => false,
                'tooltip' => __('<p>Define Magento Attribute value(s) based on which a product must ' .
                    'be relisted on the Channel.<br>Once both Relist Conditions and Advanced Conditions are met, ' .
                    'the product will be relisted.</p>'),
            ]
        );

        $fieldset->addField(
            'relist_advanced_rules_filters_warning',
            self::MESSAGES,
            [
                'messages' => [
                    [
                        'type' => \Magento\Framework\Message\MessageInterface::TYPE_WARNING,
                        'content' => __('Please be very thoughtful before enabling this option as ' .
                            'this functionality can have a negative impact on the Performance of your system.' .
                            '<br> It can decrease the speed of running in case you have a lot of Products ' .
                            'with the high number of changes made to them.'),
                    ],
                ],
            ]
        );

        $fieldset->addField(
            'relist_advanced_rules_mode',
            self::SELECT,
            [
                'name' => 'synchronization[relist_advanced_rules_mode]',
                'label' => __('Mode'),
                'value' => $formData['relist_advanced_rules_mode'],
                'values' => [
                    0 => __('Disabled'),
                    1 => __('Enabled'),
                ],
            ]
        );

        $ruleModel = $this->ruleFactory->create()
                                       ->setData(
                                           ['prefix' => TemplateSynchronization::RELIST_ADVANCED_RULES_PREFIX],
                                       );

        if (!empty($formData['relist_advanced_rules_filters'])) {
            $ruleModel->loadFromSerialized($formData['relist_advanced_rules_filters']);
        }

        $ruleBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Product\Rule::class)
                          ->setData(['rule_model' => $ruleModel]);

        $fieldset->addField(
            'advanced_filter',
            self::CUSTOM_CONTAINER,
            [
                'container_id' => 'relist_advanced_rules_filters_container',
                'label' => __('Conditions'),
                'text' => $ruleBlock->toHtml(),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
