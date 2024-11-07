<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\View\Edit;

use M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Specific\Form as AttributesForm;

class Form extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\DictionaryMapper $dictionaryMapper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\DictionaryMapper $dictionaryMapper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->dictionaryMapper = $dictionaryMapper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/saveCategoryAttributes'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\View\Edit $parentBlock */
        $parentBlock = $this->getParentBlock();
        $dictionary = $parentBlock->getDictionary();

        $form->addField(
            'dictionary_id',
            'hidden',
            [
                'name' => 'dictionary_id',
                'value' => $dictionary->getId(),
            ]
        );

        $certificationsAttributes = $this->dictionaryMapper->getCertificationsAttributes($dictionary);
        if ($certificationsAttributes !== []) {
            $fieldset = $form->addFieldset(
                'certifications_attributes_fieldset',
                [
                    'legend' => __('Product Certificates'),
                    'collapsable' => false,
                ]
            );

            $this->addAttributesTable(
                $fieldset,
                'certifications_attributes',
                $certificationsAttributes
            );
        }

        $fieldset = $form->addFieldset(
            'attributes',
            [
                'legend' => __('Product Attributes'),
                'collapsable' => false,
            ]
        );

        $virtualAttributes = $this->dictionaryMapper->getVirtualAttributes($dictionary);
        if ($virtualAttributes !== []) {
            $this->addAttributesTable($fieldset, 'virtual_attributes', $virtualAttributes);
        }

        $realAttributes = $this->dictionaryMapper->getProductAttributes($dictionary);
        if ($realAttributes !== []) {
            $this->addAttributesTable($fieldset, 'real_attributes', $realAttributes);
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $this->jsPhp->addConstants(
            [
                '\M2E\TikTokShop\Model\TikTokShop\Template\Category::VALUE_MODE_TTS_RECOMMENDED' =>
                    \M2E\TikTokShop\Model\TikTokShop\Template\Category::VALUE_MODE_TTS_RECOMMENDED,
                '\M2E\TikTokShop\Model\TikTokShop\Template\Category::VALUE_MODE_CUSTOM_VALUE' =>
                    \M2E\TikTokShop\Model\TikTokShop\Template\Category::VALUE_MODE_CUSTOM_VALUE,
                '\M2E\TikTokShop\Model\TikTokShop\Template\Category::VALUE_MODE_CUSTOM_ATTRIBUTE' =>
                    \M2E\TikTokShop\Model\TikTokShop\Template\Category::VALUE_MODE_CUSTOM_ATTRIBUTE,
                '\M2E\TikTokShop\Model\TikTokShop\Template\Category::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE' =>
                    \M2E\TikTokShop\Model\TikTokShop\Template\Category::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE,
            ]
        );

        $this->js->addRequireJs(
            [
                'etcs' => 'TikTokShop/TikTokShop/Template/Category/Specifics',
            ],
            <<<JS
        window.TikTokShopTemplateCategorySpecificsObj = new TikTokShopTemplateCategorySpecifics();
JS
        );

        return parent::_prepareLayout();
    }

    private function addAttributesTable(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        string $id,
        array $attributes
    ): void {
        /** @var AttributesForm\Renderer\Dictionary $renderer */
        $renderer = $this->getLayout()->createBlock(AttributesForm\Renderer\Dictionary::class);

        $config = [
            'specifics' => $attributes,
        ];

        $field = $fieldset->addField($id, AttributesForm\Element\Dictionary::class, $config);
        $field->setRenderer($renderer);
    }
}
