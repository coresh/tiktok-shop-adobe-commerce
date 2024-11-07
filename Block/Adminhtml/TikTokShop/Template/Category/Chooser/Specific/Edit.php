<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Specific;

class Edit extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\TikTokShop\Model\Category\Dictionary $dictionary;
    private \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\DictionaryMapper $dictionaryMapper;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary $dictionary,
        \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\DictionaryMapper $dictionaryMapper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->dictionary = $dictionary;
        $this->dictionaryMapper = $dictionaryMapper;
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('tikTokShopTemplateCategoryChooserSpecificEdit');

        $this->_controller = 'adminhtml_TikTokShop_template_category_chooser_specific';
        $this->_mode = 'edit';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    public function prepareFormData(): void
    {
        $realAttributes = $this->dictionaryMapper->getProductAttributes($this->dictionary);
        $virtualAttributes = $this->dictionaryMapper->getVirtualAttributes($this->dictionary);
        $certificationsAttributes = $this->dictionaryMapper->getCertificationsAttributes($this->dictionary);

        $formData = [
            'real_attributes' => $realAttributes,
            'virtual_attributes' => $virtualAttributes,
            'certifications_attributes' => $certificationsAttributes,
        ];

        $this->getChildBlock('form')
             ->setData('form_data', $formData);
    }

    protected function _toHtml()
    {
        $infoBlock = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Specific\Info::class,
            '',
            ['data' => ['path' => $this->dictionary->getPathWithCategoryId()]]
        );

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

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Template/Category/Specifics'
    ], function(){
        window.TikTokShopTemplateCategorySpecificsObj = new TikTokShopTemplateCategorySpecifics();
    });
JS
        );

        $parentHtml = parent::_toHtml();

        return <<<HTML
<div id="chooser_container_specific">

    <div style="margin-top: 15px;">
        {$infoBlock->_toHtml()}
    </div>

    <div id="TikTokShop-category-chooser-specific" overflow: auto;">
        {$parentHtml}
    </div>

</div>
HTML;
    }
}
