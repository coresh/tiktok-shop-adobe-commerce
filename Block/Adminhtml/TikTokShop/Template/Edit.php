<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template;

class Edit extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\TikTokShop\Helper\Url $urlHelper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\TikTokShop\Helper\Url $urlHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_tikTokShop_template';
        $this->_mode = 'edit';

        // ---------------------------------------
        $nick = $this->getTemplateNick();
        $template = $this->globalDataHelper->getValue("tiktokshop_template_$nick");
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');
        // ---------------------------------------

        // ---------------------------------------

        $isSaveAndClose = (bool)$this->getRequest()->getParam('close_on_save', false);

        // ---------------------------------------
        if ($template->getId() && !$isSaveAndClose) {
            $duplicateHeaderText = \M2E\TikTokShop\Helper\Data::escapeJs(
                (string)__('Add %template_name Policy', ['template_name' => $this->getTemplateName()]),
            );

            $onclickHandler = $nick == \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION
                ? 'TikTokShopTemplateDescriptionObj'
                : 'TikTokShopTemplateEditObj';

            $this->buttonList->add('duplicate', [
                'label' => __('Duplicate'),
                'onclick' => $onclickHandler . '.duplicateClick(
                    \'tts-template\', \'' . $duplicateHeaderText . '\', \'' . $nick . '\'
                )',
                'class' => 'add tiktokshop_duplicate_button primary',
            ]);

            $url = $this->getUrl('*/tiktokshop_template/delete');
            $this->buttonList->add('delete', [
                'label' => __('Delete'),
                'onclick' => 'TikTokShopTemplateEditObj.deleteClick(\'' . $url . '\')',
                'class' => 'delete tiktokshop_delete_button primary',
            ]);
        }
        // ---------------------------------------

        $saveConfirmation = '';
        if ($template->getId()) {
            $saveConfirmation = \M2E\TikTokShop\Helper\Data::escapeJs(
                (string)__('<br/><b>Note:</b> All changes you have made will be automatically ' .
                    'applied to all M2E TikTok Shop Connect Listings where this Policy is used.')
            );
        }

        // ---------------------------------------

        $backUrl = $this->urlHelper->makeBackUrlParam('edit');
        $url = $this->getUrl('*/tiktokshop_template/save', [
            'back' => $backUrl,
            'wizard' => $this->getRequest()->getParam('wizard'),
            'close_on_save' => $this->getRequest()->getParam('close_on_save'),
        ]);

        $saveAndBackUrl = $this->getUrl('*/tiktokshop_template/save', [
            'back' => $this->urlHelper->makeBackUrlParam('list'),
        ]);

        if ($isSaveAndClose) {
            $this->removeButton('back');

            $saveButtons = [
                'id' => 'save_and_close',
                'label' => __('Save And Close'),
                'class' => 'add',
                'button_class' => '',
                'onclick' => "TikTokShopTemplateEditObj.saveAndCloseClick('{$saveAndBackUrl}', '{$saveConfirmation}')",
                'class_name' => \M2E\TikTokShop\Block\Adminhtml\Magento\Button\SplitButton::class,
                'options' => [
                    'save' => [
                        'label' => __('Save And Continue Edit'),
                        'onclick' =>
                            "TikTokShopTemplateEditObj.saveAndEditClick('$url', '', '$saveConfirmation', '$nick');",
                    ],
                ],
            ];
        } else {
            $saveButtons = [
                'id' => 'save_and_continue',
                'label' => __('Save And Continue Edit'),
                'class' => 'add',
                'button_class' => '',
                'onclick' =>
                    "TikTokShopTemplateEditObj.saveAndEditClick('{$url}', '', '{$saveConfirmation}', '{$nick}');",
                'class_name' => \M2E\TikTokShop\Block\Adminhtml\Magento\Button\SplitButton::class,
                'options' => [
                    'save' => [
                        'label' => __('Save And Back'),
                        'onclick' =>
                            "TikTokShopTemplateEditObj.saveClick('$saveAndBackUrl', '$saveConfirmation', '$nick');",
                    ],
                ],
            ];
        }

        $this->addButton('save_buttons', $saveButtons);
    }

    public function getTemplateNick()
    {
        if (!isset($this->_data['template_nick'])) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Policy nick is not set.');
        }

        return $this->_data['template_nick'];
    }

    public function getTemplateObject()
    {
        return $this->globalDataHelper->getValue("tiktokshop_template_{$this->getTemplateNick()}");
    }

    protected function getTemplateName()
    {
        $title = '';

        switch ($this->getTemplateNick()) {
            case \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT:
                $title = __('Selling');
                break;
            case \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION:
                $title = __('Description');
                break;
            case \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION:
                $title = __('Synchronization');
                break;
            case \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_COMPLIANCE:
                $title = __('Compliance');
                break;
        }

        return $title;
    }
}
