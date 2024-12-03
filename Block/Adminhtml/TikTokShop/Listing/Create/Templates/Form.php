<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Create\Templates;

use M2E\TikTokShop\Model\Listing;
use M2E\TikTokShop\Model\ResourceModel\Template\Compliance as ComplianceResource;
use M2E\TikTokShop\Model\ResourceModel\Template\Description\CollectionFactory as DescriptionCollectionFactory;
use M2E\TikTokShop\Model\ResourceModel\Template\Compliance\CollectionFactory as ComplianceCollectionFactory;
use M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat\CollectionFactory as SellingFormatCollectionFactory;
use M2E\TikTokShop\Model\ResourceModel\Template\Synchronization\CollectionFactory as SynchronizationCollectionFactory;
use M2E\TikTokShop\Model\TikTokShop\Template\Manager as TemplateManager;

class Form extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected ?Listing $listing = null;
    private \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper;
    private Listing\Repository $listingRepository;
    private SellingFormatCollectionFactory $sellingFormatCollectionFactory;
    private SynchronizationCollectionFactory $synchronizationCollectionFactory;
    private DescriptionCollectionFactory $descriptionCollectionFactory;
    private ComplianceCollectionFactory $complianceCollectionFactory;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;

    public function __construct(
        SellingFormatCollectionFactory $sellingFormatCollectionFactory,
        SynchronizationCollectionFactory $synchronizationCollectionFactory,
        DescriptionCollectionFactory $descriptionCollectionFactory,
        ComplianceCollectionFactory $complianceCollectionFactory,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper,
        array $data = []
    ) {
        $this->sessionDataHelper = $sessionDataHelper;
        $this->listingRepository = $listingRepository;
        $this->sellingFormatCollectionFactory = $sellingFormatCollectionFactory;
        $this->synchronizationCollectionFactory = $synchronizationCollectionFactory;
        $this->descriptionCollectionFactory = $descriptionCollectionFactory;
        $this->complianceCollectionFactory = $complianceCollectionFactory;
        $this->shopRepository = $shopRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => $this->getUrl('*/tiktokshop_listing/save'),
                ],
            ]
        );

        $formData = $this->getListingData();

        $form->addField(
            'shop_id',
            'hidden',
            [
                'value' => $formData['shop_id'],
            ]
        );

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

        $templateSellingFormatValue = $formData['template_selling_format_id'];
        if (empty($templateSellingFormatValue) && !empty($sellingFormatTemplates)) {
            $templateSellingFormatValue = reset($sellingFormatTemplates)['value'];
        }

        $templateSellingFormat = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_selling_format_id',
                    'name' => 'template_selling_format_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $sellingFormatTemplates),
                    'value' => $templateSellingFormatValue,
                    'required' => true,
                ],
            ]
        );
        $templateSellingFormat->setForm($form);

        $style = count($sellingFormatTemplates) === 0 ? '' : 'display: none';
        $noPoliciesAvailableText = __('No Policies available.');
        $viewText = __('View');
        $editText = __('Edit');
        $orText = __('or');
        $addNewText = __('Add New');
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
        $noPoliciesAvailableText
    </span>
    {$templateSellingFormat->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_selling_format_template_link" style="color:#41362f">
        <a href="javascript: void(0);" style="" onclick="TikTokShopListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SELLING_FORMAT)}',
            $('template_selling_format_id').value,
            TikTokShopListingSettingsObj.newSellingFormatTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_selling_format_template_link" href="javascript: void(0);"
        onclick="TikTokShopListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['shop_id'], TemplateManager::TEMPLATE_SELLING_FORMAT)}',
        TikTokShopListingSettingsObj.newSellingFormatTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $descriptionTemplates = $this->getDescriptionTemplates();
        $style = count($descriptionTemplates) === 0 ? 'display: none' : '';

        $descriptionTemplatesValue = $formData['template_description_id'];
        if (empty($descriptionTemplatesValue) && !empty($descriptionTemplates)) {
            $descriptionTemplatesValue = reset($descriptionTemplates)['value'];
        }

        $templateDescription = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_description_id',
                    'name' => 'template_description_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $descriptionTemplates),
                    'value' => $descriptionTemplatesValue,
                    'required' => true,
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
        $noPoliciesAvailableText
    </span>
    {$templateDescription->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_description_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="TikTokShopListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_DESCRIPTION)}',
            $('template_description_id').value,
            TikTokShopListingSettingsObj.newDescriptionTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_description_template_link" href="javascript: void(0);"
        onclick="TikTokShopListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['shop_id'], TemplateManager::TEMPLATE_DESCRIPTION)}',
        TikTokShopListingSettingsObj.newDescriptionTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        // ----------------------------------------

        $this->addComplianceField($form, $fieldset);

        // ----------------------------------------

        $fieldset = $form->addFieldset(
            'synchronization_settings',
            [
                'legend' => __('Synchronization'),
                'collapsable' => false,
            ]
        );

        $synchronizationTemplates = $this->getSynchronizationTemplates();
        $style = count($synchronizationTemplates) === 0 ? 'display: none' : '';

        $templateSynchronizationValue = $formData['template_synchronization_id'];
        if (empty($templateSynchronizationValue) && !empty($synchronizationTemplates)) {
            $templateSynchronizationValue = reset($synchronizationTemplates)['value'];
        }

        $templateSynchronization = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_synchronization_id',
                    'name' => 'template_synchronization_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $synchronizationTemplates),
                    'value' => $templateSynchronizationValue,
                    'required' => true,
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
        $noPoliciesAvailableText
    </span>
    {$templateSynchronization->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_synchronization_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="TikTokShopListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
            $('template_synchronization_id').value,
            TikTokShopListingSettingsObj.newSynchronizationTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_synchronization_template_link" href="javascript: void(0);"
        onclick="TikTokShopListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['shop_id'], TemplateManager::TEMPLATE_SYNCHRONIZATION)}',
        TikTokShopListingSettingsObj.newSynchronizationTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $formData = $this->getListingData();

        $this->jsPhp->addConstants(
            \M2E\TikTokShop\Helper\Data::getClassConstants(\M2E\TikTokShop\Helper\Component\TikTokShop::class)
        );

        $this->jsUrl->addUrls(
            [
                'templateCheckMessages' => $this->getUrl('*/template/checkMessages'),
                'getShippingTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'TikTokShop_Template_Shipping',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'shop_id' => $formData['shop_id'],
                        'is_custom_template' => 0,
                    ]
                ),
                'getReturnPolicyTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'TikTokShop_Template_ReturnPolicy',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'shop_id' => $formData['shop_id'],
                        'is_custom_template' => 0,
                    ]
                ),
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
                'getComplianceTemplates' => $this->getUrl(
                    '*/general/modelGetAll',
                    [
                        'model' => 'Template_Compliance',
                        'id_field' => 'id',
                        'data_field' => 'title',
                        'sort_field' => 'title',
                        'sort_dir' => 'ASC',
                        'account_id' => $formData['account_id'],
                    ]
                ),
            ]
        );

        $this->js->addOnReadyJs(
            <<<JS
    require([
        'TikTokShop/TemplateManager',
        'TikTokShop/TikTokShop/Listing/Settings'
    ], function(){
        TemplateManagerObj = new TemplateManager();
        TikTokShopListingSettingsObj = new TikTokShopListingSettings();
        TikTokShopListingSettingsObj.initObservers();
    });
JS
        );

        return parent::_prepareLayout();
    }

    public function getDefaultFieldsValues()
    {
        return [
            'template_selling_format_id' => '',
            'template_description_id' => '',
            'template_synchronization_id' => '',
            'template_compliance_id' => '',
        ];
    }

    protected function getListingData(): ?array
    {
        if ($this->getRequest()->getParam('id') !== null) {
            $data = array_merge($this->getListing()->getData(), $this->getListing()->getData());
        } else {
            $data = $this->sessionDataHelper->getValue(Listing::CREATE_LISTING_SESSION_DATA);
            $data = array_merge($this->getDefaultFieldsValues(), $data);
        }

        return $data;
    }

    protected function getListing(): ?Listing
    {
        $listingId = $this->getRequest()->getParam('id');
        if ($this->listing === null && $listingId) {
            $this->listing = $this->listingRepository->get((int)$listingId);
        }

        return $this->listing;
    }

    protected function getSellingFormatTemplates()
    {
        $collection = $this->sellingFormatCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_ID,
                'label' => \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_TITLE,
            ]
        );

        $result = $collection->toArray();

        return $result['items'];
    }

    protected function getDescriptionTemplates()
    {
        $collection = $this->descriptionCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\TikTokShop\Model\ResourceModel\Template\Description::COLUMN_ID,
                'label' => \M2E\TikTokShop\Model\ResourceModel\Template\Description::COLUMN_TITLE,
            ]
        );

        $result = $collection->toArray();

        return $result['items'];
    }

    private function addComplianceField(
        \Magento\Framework\Data\Form\AbstractForm $form,
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset
    ): void {
        if (!$this->isRequiredComplianceTemplate()) {
            return;
        }

        $formData = $this->getListingData();

        $complianceTemplates = $this->getComplianceTemplates();
        $style = count($complianceTemplates) === 0  ? 'display: none' : '';

        $complianceTemplatesValue = $formData['template_compliance_id'];
        if (empty($complianceTemplatesValue) && !empty($complianceTemplates)) {
            $complianceTemplatesValue = reset($complianceTemplates)['value'];
        }

        $templateCompliance = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_compliance_id',
                    'name' => 'template_compliance_id',
                    'style' => 'width: 50%;' . $style,
                    'no_span' => true,
                    'values' => array_merge(['' => ''], $complianceTemplates),
                    'value' => $complianceTemplatesValue,
                    'required' => true,
                ],
            ]
        );
        $templateCompliance->setForm($form);

        $noPoliciesAvailableText = __('No Policies available.');
        $viewText = __('View');
        $editText = __('Edit');
        $orText = __('or');
        $addNewText = __('Add New');

        $style = count($complianceTemplates) === 0 ? '' : 'display: none';
        $fieldset->addField(
            'template_compliance_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Compliance Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => <<<HTML
    <span id="template_compliance_label" style="{$style}">
        $noPoliciesAvailableText
    </span>
    {$templateCompliance->toHtml()}
HTML
                ,
                'after_element_html' => <<<HTML
&nbsp;
<span style="line-height: 30px;">
    <span id="edit_compliance_template_link" style="color:#41362f">
        <a href="javascript: void(0);" onclick="TikTokShopListingSettingsObj.editTemplate(
            '{$this->getEditUrl(TemplateManager::TEMPLATE_COMPLIANCE)}',
            $('template_compliance_id').value,
            TikTokShopListingSettingsObj.newComplianceTemplateCallback
        );">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a id="add_compliance_template_link" href="javascript: void(0);"
        onclick="TikTokShopListingSettingsObj.addNewTemplate(
        '{$this->getAddNewUrl($formData['shop_id'], TemplateManager::TEMPLATE_COMPLIANCE)}',
        TikTokShopListingSettingsObj.newComplianceTemplateCallback
    );">$addNewText</a>
</span>
HTML
                ,
            ]
        );
    }

    private function getComplianceTemplates()
    {
        $accountId = $this->getListingData()['account_id'];

        $collection = $this->complianceCollectionFactory->create();
        $collection->addFieldToFilter(ComplianceResource::COLUMN_ACCOUNT_ID, ['eq' => $accountId]);
        $collection->setOrder(ComplianceResource::COLUMN_TITLE, \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)
                   ->columns(
                       [
                           'value' => ComplianceResource::COLUMN_ID,
                           'label' => ComplianceResource::COLUMN_TITLE,
                       ]
                   );

        $result = $collection->toArray();

        return $result['items'];
    }

    private function isRequiredComplianceTemplate(): bool
    {
        $shop = $this->shopRepository->get((int)$this->getListingData()['shop_id']);

        return $shop->isRegionEU();
    }

    protected function getSynchronizationTemplates(): array
    {
        $collection = $this->synchronizationCollectionFactory->create();
        $collection->addFieldToFilter('is_custom_template', 0);
        $collection->setOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(
            [
                'value' => \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization::COLUMN_ID,
                'label' => \M2E\TikTokShop\Model\ResourceModel\Template\Synchronization::COLUMN_TITLE,
            ]
        );

        return $collection->getConnection()->fetchAssoc($collection->getSelect());
    }

    protected function getAddNewUrl($shopId, $nick)
    {
        return $this->getUrl(
            '*/tiktokshop_template/newAction',
            [
                'shop_id' => $shopId,
                'wizard' => $this->getRequest()->getParam('wizard'),
                'nick' => $nick,
                'close_on_save' => 1,
            ]
        );
    }

    protected function getEditUrl($nick)
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
}
