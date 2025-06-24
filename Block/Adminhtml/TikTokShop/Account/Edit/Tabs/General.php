<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Tabs;

class General extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private ?\M2E\TikTokShop\Model\Account $account;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        ?\M2E\TikTokShop\Model\Account $account = null,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    // ----------------------------------------

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $content = __(
            'This Page shows the Environment for your %channel_title Account and details of the ' .
            'authorisation for %extension_title to connect to your %channel_title Account.<br/><br/> If your token has ' .
            'expired or is not activated, click <b>Get Token</b>.<br/><br/>',
            [
                'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle(),
                'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
            ]
        );

        $form->addField(
            'tiktokshop_accounts_general',
            self::HELP_BLOCK,
            [
                'content' => $content,
            ],
        );

        if ($this->account !== null) {
            $fieldset = $form->addFieldset(
                'general',
                [
                    'legend' => __('General'),
                    'collapsable' => false,
                ],
            );

            $fieldset->addField(
                'title',
                'text',
                [
                    'name' => 'title',
                    'class' => 'TikTokShop-account-title',
                    'label' => __('Title'),
                    'value' => $this->account->getTitle(),
                    'tooltip' => __(
                        'Title or Identifier of %channel_title Account for your internal use.',
                        [
                            'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
                        ]
                    ),
                ],
            );
        }

        $fieldset = $form->addFieldset(
            'access_details',
            [
                'legend' => __('Access Details'),
                'collapsable' => false,
            ],
        );

        $button = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Update Access Data'),
                'onclick' => 'TikTokShopAccountObj.get_token();',
                'class' => 'check tts_check_button primary',
            ],
        );

        $fieldset->addField(
            'update_access_data_container',
            'label',
            [
                'label' => '',
                'after_element_html' => $button->toHtml(),
            ],
        );

        $this->setForm($form);

        $id = $this->getRequest()->getParam('id');
        $isNeedTriggerValidation = $this->getRequest()->getParam('trigger_validation');

        $this->js->add("TikTokShop.formData.id = '$id';");

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Account'
    ], function(){
        window.TikTokShopAccountObj = new TikTokShopAccount('{$id}', '{$isNeedTriggerValidation}');
        TikTokShopAccountObj.initObservers();
    });
JS,
        );

        return parent::_prepareForm();
    }
}
