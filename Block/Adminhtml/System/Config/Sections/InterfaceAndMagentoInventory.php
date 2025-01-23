<?php

namespace M2E\TikTokShop\Block\Adminhtml\System\Config\Sections;

class InterfaceAndMagentoInventory extends \M2E\TikTokShop\Block\Adminhtml\System\Config\Sections
{
    /** @var \M2E\TikTokShop\Helper\Module\Configuration */
    private $configurationHelper;
    private \M2E\TikTokShop\Model\Product\InspectDirectChanges\Config $inspectDirectChangesConfig;

    public function __construct(
        \M2E\TikTokShop\Model\Product\InspectDirectChanges\Config $inspectDirectChangesConfig,
        \M2E\TikTokShop\Helper\Module\Configuration $configurationHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->configurationHelper = $configurationHelper;
        $this->inspectDirectChangesConfig = $inspectDirectChangesConfig;
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $form->addField(
            'interface_and_magento_inventory_help',
            self::HELP_BLOCK,
            [
                'no_collapse' => true,
                'no_hide' => true,
                'content' => __(
                    '<p>Here you can provide global settings for the Module Interface, ' .
                    'Inventory, Price. Recommendations for the tracking direct database changes can also be ' .
                    'found below. Read the <a href="%url" target="_blank">article</a> for more details.</p><br>' .
                    '<p>Click <strong>Save Config</strong> if you make any changes.</p>',
                    ['url' => 'https://docs-m2.m2epro.com/m2e-tiktok-shop-global-settings'],
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'configuration_settings_interface',
            [
                'legend' => 'Interface',
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'view_show_products_thumbnails_mode',
            self::SELECT,
            [
                'name' => 'groups[interface][fields][view_show_products_thumbnails_mode][value]',
                'label' => __('Products Thumbnail'),
                'values' => [
                    0 => __('Do Not Show'),
                    1 => __('Show'),
                ],
                'value' => $this->configurationHelper->getViewShowProductsThumbnailsMode(),
                'tooltip' => __('Choose whether you want to see Thumbnail Images for ' .
                    'Products on the Add Products and View Listing Pages.'),
            ]
        );

        $fieldset->addField(
            'view_show_block_notices_mode',
            self::SELECT,
            [
                'name' => 'groups[interface][fields][view_show_block_notices_mode][value]',
                'label' => __('Help Information'),
                'values' => [
                    0 => __('Do Not Show'),
                    1 => __('Show'),
                ],
                'value' => $this->configurationHelper->getViewShowBlockNoticesMode(),
                'tooltip' => __('<p>Choose whether you want the help information to be available at the top of ' .
                    'each M2E TikTok Shop Connect Page.</p><br><p><strong>Please note</strong>, it does not disable the ' .
                    'help-tips (the icons with the additional information next to the main options).</p>'),
            ]
        );

        $fieldset->addField(
            'restore_block_notices',
            self::BUTTON,
            [
                'label' => '',
                'content' => __('Restore All Helps & Remembered Choices'),
                'field_extra_attributes' => 'id="restore_block_notices_tr"',
            ]
        );

        $fieldset = $form->addFieldset(
            'configuration_settings_magento_inventory_quantity',
            [
                'legend' => __('Quantity & Price'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'product_force_qty_mode',
            self::SELECT,
            [
                'name' => 'groups[quantity_and_price][fields][product_force_qty_mode][value]',
                'label' => __('Manage Stock "No", Backorders'),
                'values' => [
                    0 => __('Disallow'),
                    1 => __('Allow'),
                ],
                'value' => $this->configurationHelper->isEnableProductForceQtyMode(),
                'tooltip' => __('Choose whether M2E TikTok Shop Connect is allowed to List Products with unlimited ' .
                    'stock or that are temporarily out of stock.<br><b>Disallow</b> is the recommended setting ' .
                    'for TikTok Shop Integration.'),
            ]
        );

        $fieldset->addField(
            'product_force_qty_value',
            self::TEXT,
            [
                'name' => 'groups[quantity_and_price][fields][product_force_qty_value][value]',
                'label' => __('Quantity To Be Listed'),
                'value' => $this->configurationHelper->getProductForceQtyValue(),
                'tooltip' => __('Set a number to List, e.g. if you have Manage Stock "No" in Magento Product ' .
                    'and set this Value to 10, 10 will be sent as available Quantity to the Channel.'),
                'field_extra_attributes' => 'id="product_force_qty_value_tr"',
                'class' => 'validate-greater-than-zero',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'magento_attribute_price_type_converting_mode',
            self::SELECT,
            [
                'name' => 'groups[quantity_and_price][fields][magento_attribute_price_type_converting_mode][value]',
                'label' => __('Convert Magento Price Attribute'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $this->configurationHelper
                    ->getMagentoAttributePriceTypeConvertingMode(),
                'tooltip' => __('<p>Choose whether Magento Price Attribute values should be converted ' .
                    'automatically. With this option enabled, M2E TikTok Shop Connect will provide currency conversion ' .
                    'based on Magento Currency Settings.</p><p><strong>For example</strong>, the Item Price is set ' .
                    'to be taken from Magento Price Attribute (e.g. 5 USD).<br>If this Item is listed on Shop ' .
                    'with a different Base Currency (e.g. GBP), the currency conversion is performed automatically ' .
                    'based on the set exchange rate (e.g. 1 USD = 0.82 GBP).<br>The Item will be available on Channel ' .
                    'at the Price of 4.1 GBP.</p>'),
            ]
        );

        $fieldset = $form->addFieldset(
            'direct_database_changes_field',
            [
                'legend' => __('Direct Database Changes'),
                'collabsable' => false,
            ]
        );

                $inspectorMode = $this->inspectDirectChangesConfig->isEnableProductInspectorMode();

                $fieldset->addField(
                    'listing_product_inspector_mode',
                    self::SELECT,
                    [
                        'name' => 'groups[direct_database_changes][fields][listing_product_inspector_mode][value]',
                        'label' => __('Track Direct Database Changes'),
                        'values' => [
                            ['value' => 0, 'label' => __('No')],
                            ['value' => 1, 'label' => __('Yes')],
                        ],
                        'value' => $inspectorMode,
                        'tooltip' => __(
                            'Enable this option to ensure M2E detects changes when Magento Product information' .
                            ' is updated not through Magento Core Models (e.g., direct SQL updates)'
                        ),
                    ]
                );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->jsUrl->add(
            $this->getUrl('m2e_tiktokshop/settings_interfaceAndMagentoInventory/restoreRememberedChoices'),
            'settings_interface/restoreRememberedChoices'
        );

        $this->jsTranslator->add(
            'Help Blocks have been restored.',
            __('Help Blocks have been restored.')
        );

        $this->js->addRequireJs(
            [
                'j' => 'jquery',
            ],
            <<<JS
$('view_show_block_notices_mode').observe('change', function() {
    if ($('view_show_block_notices_mode').value === '1') {
        $('restore_block_notices_tr').show();
    } else {
        $('restore_block_notices_tr').hide();
    }
}).simulate('change');

$('restore_block_notices').observe('click', function() {
    SettingsObj.restoreAllHelpsAndRememberedChoices();
});

$('product_force_qty_mode').observe('change', function() {
    if($('product_force_qty_mode').value === '1') {
        $('product_force_qty_value_tr').show();
    } else {
        $('product_force_qty_value_tr').hide();
    }
}).simulate('change');
JS
        );
    }
}
