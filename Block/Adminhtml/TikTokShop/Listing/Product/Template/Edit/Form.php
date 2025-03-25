<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Product\Template\Edit;

use M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat\CollectionFactory as SellingFormatCollectionFactory;
use M2E\TikTokShop\Model\TikTokShop\Template\Manager as TemplateManager;

class Form extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private const VALUE_USE_FROM_LISTING = '';
    private const VALUE_DIFFERENT_TEMPLATES = '0';

    private \M2E\TikTokShop\Helper\Data\GlobalData $helperDataGlobal;
    private SellingFormatCollectionFactory $sellingFormatCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization\CollectionFactory $syncCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\GlobalData $helperDataGlobal,
        SellingFormatCollectionFactory $sellingFormatCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Template\Description\CollectionFactory $descriptionCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization\CollectionFactory $syncCollectionFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->helperDataGlobal = $helperDataGlobal;
        $this->sellingFormatCollectionFactory = $sellingFormatCollectionFactory;
        $this->descriptionCollectionFactory = $descriptionCollectionFactory;
        $this->syncCollectionFactory = $syncCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Form
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm(): Form
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => $this->getUrl('*/tiktokshop_template/save'),
                ],
            ]
        );

        $formData = $this->getListingProductsData();

        $store = $this->helperDataGlobal->getValue('tiktokshop_store');

        $formData['store_id'] = $store->getId();

        $form->addField(
            'store_id',
            'hidden',
            [
                'value' => $formData['store_id'],
            ]
        );

        $fieldset = $form->addFieldset(
            'selling_settings',
            [
                'legend' => __('Selling'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'template_selling_format_messages',
            self::CUSTOM_CONTAINER,
            [
                'style' => 'display: block;',
                'css_class' => 'TikTokShop-fieldset-table no-margin-bottom',
            ]
        );

        $sellingFormatTemplates = $this->getSellingFormatTemplates();
        $style = count($sellingFormatTemplates) === 0 ? 'display: none' : '';

        $templateSellingFormat = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_selling_format_id',
                    'name' => 'template_selling_format_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => $this->getTemplateValues($sellingFormatTemplates),
                    'value' => $formData['template_selling_format_id'],
                    'class' => 'template-switcher TikTokShop-validate-template-switcher'
                        . ' listing-policy-template-switcher',
                ],
            ]
        );
        $templateSellingFormat->setForm($form);

        $style = count($sellingFormatTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_selling_format_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Selling Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_selling_format_label" style="{$style}">
        {$this->__('No Policies available.')}
    </span>
    {$templateSellingFormat->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_selling_format_template_link" style="color:#41362f">
        <a href="javascript: void(0);" style="" onclick="TikTokShopListingProductSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SELLING_FORMAT)}',
            $('template_selling_format_id').value,
            TikTokShopListingProductSettingsObj.newSellingFormatTemplateCallback
        );">
            {$this->__('View')}&nbsp;/&nbsp;{$this->__('Edit')}
        </a>
        <span>{$this->__('or')}</span>
    </span>
    <a id="add_selling_format_template_link" href="javascript: void(0);"
        onclick="TikTokShopListingProductSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl(TemplateManager::TEMPLATE_SELLING_FORMAT)}',
        TikTokShopListingProductSettingsObj.newSellingFormatTemplateCallback
    );">{$this->__('Add New')}</a>
    <span id="specify_selling_template_link" class="specify-template-span">
        {$this->__('Please, specify a value suitable for all chosen Products.')}
    </span>
</span>
HTML
                ,
            ]
        );

        $descriptionTemplates = $this->getDescriptionTemplates();
        $style = count($descriptionTemplates) === 0 ? 'display: none' : '';

        $templateDescription = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_description_id',
                    'name' => 'template_description_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => $this->getTemplateValues($descriptionTemplates),
                    'value' => $formData['template_description_id'],
                    'class' => 'template-switcher TikTokShop-validate-template-switcher'
                        . ' listing-policy-template-switcher',
                ],
            ]
        );
        $templateDescription->setForm($form);

        $style = count($descriptionTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_description_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Description Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_description_label" style="{$style}">
        {$this->__('No Policies available.')}
    </span>
    {$templateDescription->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_description_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="TikTokShopListingProductSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_DESCRIPTION)}',
            $('template_description_id').value,
            TikTokShopListingProductSettingsObj.newDescriptionTemplateCallback
        );">
            {$this->__('View')}&nbsp;/&nbsp;{$this->__('Edit')}
        </a>
        <span>{$this->__('or')}</span>
    </span>
    <a id="add_description_template_link" href="javascript: void(0);"
        onclick="TikTokShopListingProductSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl( TemplateManager::TEMPLATE_DESCRIPTION)}',
        TikTokShopListingProductSettingsObj.newDescriptionTemplateCallback
    );">{$this->__('Add New')}</a>
    <span id="specify_description_template_link" class="specify-template-span">
        {$this->__('Please, specify a value suitable for all chosen Products.')}
    </span>
</span>
HTML
                ,
            ]
        );

        $fieldset = $form->addFieldset(
            'synchronization_settings',
            [
                'legend' => __('Synchronization'),
                'collapsable' => false,
            ]
        );

        $synchronizationTemplates = $this->getSynchronizationTemplates();
        $style = count($synchronizationTemplates) === 0 ? 'display: none' : '';

        $templateSynchronization = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_synchronization_id',
                    'name' => 'template_synchronization_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => $this->getTemplateValues($synchronizationTemplates),
                    'value' => $formData['template_synchronization_id'],
                    'class' => 'template-switcher TikTokShop-validate-template-switcher'
                        . ' listing-policy-template-switcher',
                ],
            ]
        );
        $templateSynchronization->setForm($form);

        $style = count($synchronizationTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_synchronization_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Synchronization Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_synchronization_label" style="{$style}">
        {$this->__('No Policies available.')}
    </span>
    {$templateSynchronization->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_synchronization_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="TikTokShopListingProductSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
            $('template_synchronization_id').value,
            TikTokShopListingProductSettingsObj.newSynchronizationTemplateCallback
        );">
            {$this->__('View')}&nbsp;/&nbsp;{$this->__('Edit')}
        </a>
        <span>{$this->__('or')}</span>
    </span>
    <a id="add_synchronization_template_link" href="javascript: void(0);"
        onclick="TikTokShopListingProductSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl(TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
        TikTokShopListingProductSettingsObj.newSynchronizationTemplateCallback
    );">{$this->__('Add New')}</a>
    <span id="specify_synchronization_template_link" class="specify-template-span">
        {$this->__('Please, specify a value suitable for all chosen Products.')}
    </span>
</span>
HTML
                ,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Product\Template\Edit\Form|\M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \ReflectionException
     */
    protected function _prepareLayout()
    {
        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Helper\Component\TikTokShop::class)
        );

        $this->jsUrl->addUrls(
            [
                'templateCheckMessages' => $this->getUrl('*/template/checkMessages'),

                'getSellingFormatTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Template_SellingFormat',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'is_custom_template' => 0,
                    ]
                ),
                'getDescriptionTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Template_Description',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'is_custom_template' => 0,
                    ]
                ),
                'getSynchronizationTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Template_Synchronization',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'is_custom_template' => 0,
                    ]
                ),
            ]
        );

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TemplateManager',
        'TikTokShop/TikTokShop/Listing/Product/Settings'
    ], function() {
        TemplateManagerObj = new TemplateManager();
        TikTokShopListingProductSettingsObj = new TikTokShopListingProductSettings();
        TikTokShopListingProductSettingsObj.initObservers();
    });
JS
        );

        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getSellingFormatTemplates()
    {
        $collection = $this->sellingFormatCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => 'id',
                'label' => 'title',
            ]
        );

        $result = $collection->toArray();

        return $result['items'];
    }

    /**
     * @return mixed
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getDescriptionTemplates()
    {
        $collection = $this->descriptionCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => 'id',
                'label' => 'title',
            ]
        );

        $result = $collection->toArray();

        return $result['items'];
    }

    /**
     * @return array
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getSynchronizationTemplates(): array
    {
        $collection = $this->syncCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => 'id',
                'label' => 'title',
            ]
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    /**
     * @param mixed $nick
     *
     * @return string
     */
    private function getAddNewUrl($nick): string
    {
        return $this->getUrl(
            '*/tiktokshop_template/newAction',
            [
                'wizard' => $this->getRequest()->getParam('wizard'),
                'nick' => $nick,
                'close_on_save' => 1,
            ]
        );
    }

    /**
     * @param mixed $nick
     *
     * @return string
     */
    private function getEditUrl($nick): string
    {
        return $this->getUrl(
            '*/tiktokshop_template/edit',
            [
                'wizard' => $this->getRequest()->getParam('wizard'),
                'nick' => $nick,
                'close_on_save' => 1,
            ]
        );
    }

    /**
     * @param mixed $template
     *
     * @return array
     */
    private function getTemplateValues($template): array
    {
        return [
            [
                'value' => self::VALUE_DIFFERENT_TEMPLATES,
                'label' => '',
            ],
            [
                'value' => self::VALUE_USE_FROM_LISTING,
                'label' => 'Use From Listing Settings',
            ],
            [
                'value' => $template,
                'label' => 'Policies',
            ],
        ];
    }

    /**
     * @return array
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getListingProductsData(): array
    {
        $templates = [
            'selling_format',
            'description',
            'synchronization',
        ];

        $resultData = [];

        foreach ($templates as $templateName) {
            if (!$this->helperDataGlobal->getValue('tiktokshop_template_force_parent_' . $templateName)) {
                $templateData = $this->helperDataGlobal->getValue('tiktokshop_template_' . $templateName);
                $resultData['template_' . $templateName . '_id'] = $templateData->getId();
            } else {
                $resultData['template_' . $templateName . '_id'] = self::VALUE_DIFFERENT_TEMPLATES;
            }
        }

        return $resultData;
    }
}
