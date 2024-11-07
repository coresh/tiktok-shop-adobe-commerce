<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Edit;

class Form extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalDataHelper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('tiktokshopTemplateEditForm');
        // ---------------------------------------

        $this->css->addFile('tiktokshop/template.css');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => 'javascript:void(0)',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $fieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General'), 'collapsable' => false]
        );

        $templateData = $this->getTemplateData();

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'value' => $templateData['title'],
                'class' => 'input-text validate-title-uniqueness',
                'required' => true,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    public function getTemplateData()
    {
        $nick = $this->getTemplateNick();
        $templateData = $this->globalDataHelper->getValue("tiktokshop_template_$nick");

        return array_merge([
            'title' => '',
        ], $templateData->getData());
    }

    public function getTemplateNick()
    {
        return $this->getParentBlock()->getTemplateNick();
    }

    public function getTemplateId()
    {
        $template = $this->getParentBlock()->getTemplateObject();

        return $template ? $template->getId() : null;
    }

    protected function _toHtml()
    {
        $nick = $this->getTemplateNick();
        $this->jsUrl->addUrls([
            'tiktokshop_template/getTemplateHtml' => $this->getUrl(
                '*/tiktokshop_template/getTemplateHtml',
                [
                    'account_id' => null,
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                    'mode' => \M2E\TikTokShop\Model\TikTokShop\Template\Manager::MODE_TEMPLATE,
                    'data_force' => true,
                ]
            ),
            'tiktokshop_template/isTitleUnique' => $this->getUrl(
                '*/tiktokshop_template/isTitleUnique',
                [
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                ]
            ),
            'deleteAction' => $this->getUrl(
                '*/tiktokshop_template/delete',
                [
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                ]
            ),
        ]);

        $this->jsTranslator->addTranslations([
            'Policy Title is not unique.' => __('Policy Title is not unique.'),
            'Do not show any more' => __('Do not show this message anymore'),
            'Save Policy' => __('Save Policy'),
        ]);

        $this->js->addRequireJs(
            [
                'form' => 'TikTokShop/TikTokShop/Template/Edit/Form',
                'jquery' => 'jquery',
            ],
            <<<JS

        window.TikTokShopTemplateEditObj = new TikTokShopTemplateEdit();
        TikTokShopTemplateEditObj.templateNick = '{$this->getTemplateNick()}';
        TikTokShopTemplateEditObj.initObservers();
JS
        );

        return parent::_toHtml();
    }
}
