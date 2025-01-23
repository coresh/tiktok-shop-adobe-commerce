<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Description\Edit\Form;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;
use M2E\TikTokShop\Model\ResourceModel\Template\Description as DescriptionResource;
use M2E\TikTokShop\Model\Template\Description as DescriptionTemplate;
use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Description\Renderer;

class Data extends AbstractForm
{
    private \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper;
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;
    private DescriptionTemplate\BuilderFactory $templateDescriptionBuilderFactory;
    private array $magentoAttributes = [];

    public function __construct(
        \M2E\TikTokShop\Model\Template\Description\BuilderFactory $templateDescriptionBuilderFactory,
        \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->templateDescriptionBuilderFactory = $templateDescriptionBuilderFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->magentoAttributes = $this->magentoAttributeHelper->getAll();
    }

    protected function _prepareForm()
    {
        $imgAttributes = $this->magentoAttributeHelper->filterByInputTypes(
            $this->magentoAttributes,
            ['text', 'image', 'media_image', 'gallery', 'multiline', 'textarea', 'select', 'multiselect']
        );

        $formData = $this->getFormData();

        $default = $this->getDefaultTemplateData();
        $formData = array_replace_recursive($default, $formData);

        $form = $this->_formFactory->create();
        $this->setForm($form);

        $form->addField(
            'description_id',
            'hidden',
            [
                'name' => 'description[id]',
                'value' => (!$this->isCustom() && isset($formData['id'])) ? (int)$formData['id'] : '',
            ]
        );

        $form->addField(
            'description_title',
            'hidden',
            [
                'name' => 'description[title]',
                'value' => $this->getTitle(),
            ]
        );

        $form->addField(
            'description_is_custom_template',
            'hidden',
            [
                'name' => 'description[is_custom_template]',
                'value' => $this->isCustom() ? 1 : 0,
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_description_form_data_image',
            [
                'legend' => __('Images'),
                'collapsable' => true,
            ]
        );

        $preparedAttributes = [];
        foreach ($imgAttributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];
            if (
                $formData[DescriptionResource::COLUMN_IMAGE_MAIN_MODE]
                == DescriptionTemplate::IMAGE_MAIN_MODE_ATTRIBUTE
                && $formData[DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE] == $attribute['code']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => DescriptionTemplate::IMAGE_MAIN_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'image_main',
            self::SELECT,
            [
                'name' => 'description[image_main_mode]',
                'label' => __('Main Image'),
                'values' => [
                    DescriptionTemplate::IMAGE_MAIN_MODE_PRODUCT => __('Product Base Image'),
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'value' => $formData[DescriptionResource::COLUMN_IMAGE_MAIN_MODE]
                != DescriptionTemplate::IMAGE_MAIN_MODE_ATTRIBUTE
                    ? $formData[DescriptionResource::COLUMN_IMAGE_MAIN_MODE]
                    : '',
                'create_magento_attribute' => true,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,textarea,select,multiselect');

        $fieldset->addField(
            'image_main_attribute',
            'hidden',
            [
                'name' => 'description[image_main_attribute]',
                'value' => $formData[DescriptionResource::COLUMN_IMAGE_MAIN_ATTRIBUTE],
            ]
        );

        $fieldset->addField(
            'gallery_images_limit',
            'hidden',
            [
                'name' => 'description[gallery_images_limit]',
                'value' => $formData[DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT],
            ]
        );

        $fieldset->addField(
            'gallery_images_attribute',
            'hidden',
            [
                'name' => 'description[gallery_images_attribute]',
                'value' => $formData[DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE],
            ]
        );

        $preparedImages = [];
        for ($upToImageNumber = 1; $upToImageNumber <= DescriptionTemplate\Source::GALLERY_IMAGES_COUNT_MAX; $upToImageNumber++) {
            $attrs = ['attribute_code' => $upToImageNumber];

            if (
                $upToImageNumber == $formData[DescriptionResource::COLUMN_GALLERY_IMAGES_LIMIT]
                && $formData[DescriptionResource::COLUMN_GALLERY_IMAGES_MODE]
                == DescriptionTemplate::GALLERY_IMAGES_MODE_PRODUCT
            ) {
                $attrs['selected'] = 'selected';
            }

            $preparedImages[] = [
                'value' => DescriptionTemplate::GALLERY_IMAGES_MODE_PRODUCT,
                'label' => $upToImageNumber == 1 ? $upToImageNumber : (__('Up to') . " $upToImageNumber"),
                'attrs' => $attrs,
            ];
        }

        $preparedAttributes = [];
        foreach ($imgAttributes as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];

            if (
                $formData[DescriptionResource::COLUMN_GALLERY_IMAGES_MODE]
                == DescriptionTemplate::GALLERY_IMAGES_MODE_ATTRIBUTE
                && $formData[DescriptionResource::COLUMN_GALLERY_IMAGES_ATTRIBUTE] == $attribute['code']
            ) {
                $attrs['selected'] = 'selected';
            }

            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => DescriptionTemplate::GALLERY_IMAGES_MODE_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'gallery_images',
            self::SELECT,
            [
                'container_id' => 'gallery_images_mode_tr',
                'name' => 'description[gallery_images_mode]',
                'label' => __('Gallery Images'),
                'values' => [
                    DescriptionTemplate::GALLERY_IMAGES_MODE_NONE => __('None'),
                    [
                        'label' => __('Product Images'),
                        'value' => $preparedImages,
                    ],
                    [
                        'label' => __('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'is_magento_attribute' => true,
                        ],
                    ],
                ],
                'create_magento_attribute' => true,
                'tooltip' => __('Adds small thumbnails that appear under the large Base Image. You can ' .
                    'add up to 8 additional photos to each Listing on TikTok Shop. <br/><b>Note:</b> ' .
                    'Text, Multiple Select or Dropdown type Attribute can be used. The value of Attribute must ' .
                    'contain absolute urls. <br/>In Text type Attribute urls must be separated with comma.' .
                    '<br/>e.g. http://mymagentostore.com/images/baseimage1.jpg, ' .
                    'http://mymagentostore.com/images/baseimage2.jpg'),
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,textarea,select,multiselect');

        $fieldset->addField(
            'image_resize',
            self::SELECT,
            [
                'name' => 'description[resize_image]',
                'label' => __('Resize Image'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData[DescriptionResource::COLUMN_RESIZE_IMAGE],
                'tooltip' => __(
                    'M2E will automatically resize Product Images to ' .
                    'meet TTS media requirements before synchronizing them to the channel'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_template_description_form_data_description',
            [
                'legend' => __('Description'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'title_mode',
            'select',
            [
                'label' => __('Title'),
                'name' => 'description[title_mode]',
                'values' => [
                    DescriptionTemplate::TITLE_MODE_PRODUCT => __('Product Name'),
                    DescriptionTemplate::TITLE_MODE_CUSTOM => __('Custom Value'),
                ],
                'value' => $formData[DescriptionResource::COLUMN_TITLE_MODE],
                'tooltip' => __(
                    'This is the Title that Buyers will see on TikTok Shop. A good Title ensures better visibility.'
                ),
            ]
        );

        $preparedAttributes = [];
        foreach ($this->magentoAttributes as $attribute) {
            $preparedAttributes[] = [
                'value' => $attribute['code'],
                'label' => $attribute['label'],
            ];
        }

        $button = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Magento\Button\MagentoAttribute::class
        )
                       ->addData(
                           [
                               'label' => __('Insert'),
                               'destination_id' => 'title_template',
                               'class' => 'primary',
                               'style' => 'display: inline-block;',
                           ]
                       );

        $selectAttrBlock = $this->elementFactory->create(
            self::SELECT,
            [
                'data' => [
                    'values' => $preparedAttributes,
                    'class' => 'TikTokShop-required-when-visible magento-attribute-custom-input',
                    'create_magento_attribute' => true,
                ],
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text,select,multiselect,boolean,price,date')
                                                ->addCustomAttribute('apply_to_all_attribute_sets', 'false');

        $selectAttrBlock->setId('selectAttr_title_template');
        $selectAttrBlock->setForm($this->_form);

        $fieldset->addField(
            'title_template',
            'text',
            [
                'container_id' => 'custom_title_tr',
                'label' => __('Title Value'),
                'value' => $formData[DescriptionResource::COLUMN_TITLE_TEMPLATE],
                'name' => 'description[title_template]',
                'class' => 'input-text-title',
                'required' => true,
                'after_element_html' => $selectAttrBlock->toHtml() . $button->toHtml(),
            ]
        );

        $button = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
            ->addData([
                'label' => __('Preview'),
                'onclick' => 'TikTokShopTemplateDescriptionObj.openPreviewPopup()',
                'class' => 'action-primary',
                'style' => 'margin-left: 70px;',
            ]);

        $tooltipMessage = (string)__(
            'Choose whether to use Magento <strong>Product Description</strong> ' .
            'or <strong>Product Short Description</strong> for the TikTok Listing Description.'
        );

        $fieldset->addField(
            'description_mode',
            'select',
            [
                'label' => __('Description'),
                'name' => 'description[description_mode]',
                'values' => [
                    DescriptionTemplate::DESCRIPTION_MODE_PRODUCT => __('Product Description'),
                    DescriptionTemplate::DESCRIPTION_MODE_SHORT => __('Product Short Description'),
                    DescriptionTemplate::DESCRIPTION_MODE_CUSTOM => __('Custom Value'),
                ],
                'value' => $this->isEdit() ? $formData[DescriptionResource::COLUMN_DESCRIPTION_MODE] : '-1',
                'class' => 'TikTokShop-validate-description-mode',
                'required' => true,
                'after_element_html' => $this->getTooltipHtml($tooltipMessage) . $button->toHtml(),
            ]
        );

        $isCustomDescription = $formData[DescriptionResource::COLUMN_DESCRIPTION_MODE]
            == DescriptionTemplate::DESCRIPTION_MODE_CUSTOM;

        if ($isCustomDescription) {
            $fieldset->addField(
                'view_edit_custom_description_link',
                'link',
                [
                    'container_id' => 'view_edit_custom_description',
                    'label' => '',
                    'value' => __('View / Edit Custom Description'),
                    'onclick' => 'TikTokShopTemplateDescriptionObj.view_edit_custom_change()',
                    'href' => 'javascript://',
                    'style' => 'text-decoration: underline;',
                ]
            );
        }

        $openCustomInsertsButton = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
            ->setData([
                'id' => 'custom_inserts_open_popup',
                'label' => __('Insert Customs'),
                'class' => 'action-primary',
            ]);

        $fieldset->addField(
            'description_template',
            'editor',
            [
                'container_id' => 'description_template_tr',
                'css_class' => 'c-custom_description_tr _required',
                'label' => __('Description Value'),
                'name' => 'description[description_template]',
                'value' => $formData[DescriptionResource::COLUMN_DESCRIPTION_TEMPLATE],
                'class' => ' admin__control-textarea left TikTokShop-validate-description-template',
                'after_element_html' => sprintf(
                    '<div id="description_template_buttons">%s</div>',
                    $openCustomInsertsButton->toHtml()
                ),
            ]
        );

        $this->jsPhp->addConstants([
            '\\M2E\\TikTokShop\\Model\\Template\\Description::DESCRIPTION_MODE_CUSTOM'
            => DescriptionTemplate::DESCRIPTION_MODE_CUSTOM
        ]);

        $this->jsUrl->addUrls(
            [
                'tiktokshop_template_description/checkMagentoProductId' => $this->getUrl(
                    '*/tiktokshop_template_description/checkMagentoProductId'
                ),
                'tiktokshop_template_description/getRandomMagentoProductId' => $this->getUrl(
                    '*/tiktokshop_template_description/getRandomMagentoProductId'
                ),
                'tiktokshop_template_description/preview' => $this->getUrl(
                    '*/tiktokshop_template_description/preview'
                ),
            ]
        );

        $this->jsTranslator->addTranslations(
            [
                'Adding Image' => (string)__('Adding Image'),
                'Custom Insertions' => (string)__('Custom Insertions'),
                'Show Editor' => (string)__('Show Editor'),
                'Hide Editor' => (string)__('Hide Editor'),
                'Description Preview' => (string)__('Description Preview'),
                'Please enter a valid Magento product ID.' => (string)__('Please enter a valid Magento product ID.'),
                'Please enter Description Value.' => (string)__('Please enter Description Value.'),
            ]
        );

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Template/Description',
        'TikTokShop/Plugin/Magento/Attribute/Button'
    ], function(){
        window.TikTokShopTemplateDescriptionObj = new TikTokShopTemplateDescription();
        setTimeout(function() {
            TikTokShopTemplateDescriptionObj.initObservers();
        }, 50);

        window.MagentoAttributeButtonObj = new MagentoAttributeButton();
    });
JS
        );

        return parent::_prepareForm();
    }

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getCustomInsertsHtml()
            . $this->getDescriptionPreviewHtml();
    }

    private function isCustom(): bool
    {
        if (isset($this->_data['is_custom'])) {
            return (bool)$this->_data['is_custom'];
        }

        return false;
    }

    private function isEdit(): bool
    {
        $template = $this->getDescriptionTemplate();

        if ($template === null || $template->getId() === null) {
            return false;
        }

        return true;
    }

    private function getTitle(): string
    {
        if ($this->isCustom()) {
            return $this->_data['custom_title'] ?? '';
        }

        $template = $this->getDescriptionTemplate();

        if (!$this->isEdit()) {
            return '';
        }

        return $template->getTitle();
    }

    private function getFormData()
    {
        if (!$this->isEdit()) {
            return [];
        }

        $template = $this->getDescriptionTemplate();

        return $template->getData();
    }

    private function getDefaultTemplateData(): array
    {
        return $this->templateDescriptionBuilderFactory->create()->getDefaultData();
    }

    private function getCustomInsertsHtml(): string
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('custom_inserts', ['legend' => __('Attribute')]);

        $preparedAttributes = [];
        foreach ($this->magentoAttributes as $attribute) {
            $preparedAttributes[] = [
                'value' => $attribute['code'],
                'label' => $attribute['label'],
            ];
        }

        $button = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)->setData(
            [
                'label' => __('Insert'),
                'class' => 'action-primary',
                'onclick' => 'TikTokShopTemplateDescriptionObj.insertProductAttribute()',
                'style' => 'margin-left: 15px;',
            ]
        );

        $fieldset->addField(
            'custom_inserts_product_attribute',
            self::SELECT,
            [
                'label' => __('Magento Product'),
                'class' => 'TikTokShop-custom-attribute-can-be-created',
                'values' => $preparedAttributes,
                'after_element_html' => $button->toHtml(),
                'apply_to_all_attribute_sets' => 0,
            ]
        )->addCustomAttribute('apply_to_all_attribute_sets', 0);

        $TikTokShopAttributes = [
            Renderer::TTS_ATTRIBUTE_CODE_TITLE => __('Title'),
            Renderer::TTS_ATTRIBUTE_CODE_PRICE => __('TikTok Shop Price'),
            Renderer::TTS_ATTRIBUTE_CODE_QTY => __('QTY'),
        ];

        $button->setData('onclick', 'TikTokShopTemplateDescriptionObj.insertTikTokShopAttribute()');

        $fieldset->addField(
            'custom_inserts_tiktokshop_attribute',
            'select',
            [
                'label' => __('M2E TikTok Shop Connect'),
                'values' => $TikTokShopAttributes,
                'after_element_html' => $button->toHtml(),
            ]
        );

        return <<<HTML
<div class="hidden">
    <div id="custom_inserts_popup" class="admin__old">{$form->toHtml()}</div>
</div>
HTML;
    }

    private function getDescriptionPreviewHtml(): string
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('description_preview_fieldset', ['legend' => '']);

        $fieldset->addField(
            'description_preview_help_block',
            self::HELP_BLOCK,
            [
                'content' => __('If you would like to preview the Description data for the particular ' .
                    'Magento Product, please, provide its ID into the <strong>Magento Product ID</strong> ' .
                    'input and select a <strong>Magento Store View</strong> the values should be taken from. ' .
                    'As a result you will see the Item Description which will be sent to TikTok Shop basing on ' .
                    'the settings you specified.<br />Also, you can press a <strong>Select Randomly</strong> ' .
                    'button to allow M2E TikTok Shop Connect to automatically select the most suitable Product for ' .
                    'its previewing.'),
            ]
        );

        $button = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Select Randomly'),
                'onclick' => 'TikTokShopTemplateDescriptionObj.selectProductIdRandomly()',
                'class' => 'action-primary',
                'style' => 'margin-left: 25px',
            ]
        );

        $fieldset->addField(
            'description_preview_magento_product_id',
            'text',
            [
                'label' => __('Magento Product ID'),
                'after_element_html' => $button->toHtml(),
                'class' => 'TikTokShop-required-when-visible validate-digits
                                         TikTokShop-validate-magento-product-id',
                'css_class' => '_required',
                'style' => 'width: 200px',
                'name' => 'description_preview[magento_product_id]',
            ]
        );

        $fieldset->addField(
            'description_preview_store_id',
            self::STORE_SWITCHER,
            [
                'label' => __('Store View'),
                'name' => 'description_preview[store_id]',
            ]
        );

        $fieldset->addField(
            'description_preview_description_mode',
            'hidden',
            [
                'name' => 'description_preview[description_mode]',
            ]
        );
        $fieldset->addField(
            'description_preview_description_template',
            'hidden',
            [
                'name' => 'description_preview[description_template]',
            ]
        );

        $fieldset->addField(
            'description_preview_form_key',
            'hidden',
            [
                'name' => 'form_key',
                'value' => $this->formKey->getFormKey(),
            ]
        );

        return <<<HTML
<div class="hidden">
    <div id="description_preview_popup" class="admin__old">{$form->toHtml()}</div>
</div>
HTML;
    }

    private function getDescriptionTemplate(): ?DescriptionTemplate
    {
        return $this->globalDataHelper->getValue('tiktokshop_template_description');
    }
}
