<?php

namespace M2E\TikTokShop\Block\Adminhtml\Wizard\Installation\Registration;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;

abstract class Content extends AbstractForm
{
    private \M2E\Core\Block\Adminhtml\RegistrationForm $form;

    public function __construct(
        \M2E\Core\Block\Adminhtml\RegistrationForm $form,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->form = $form;
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            __(
                '%extension_title requires activation for further work. ' .
                'To activate your installation, you should obtain a <strong>License Key</strong>. For more details, ' .
                'please read our <a href="%privacy" target="_blank">Privacy Policy</a>.<br/><br/> Fill out the form ' .
                'below with the required information. This information will be used to register you on ' .
                '<a href="%urk" target="_blank">M2E Accounts</a> and auto-generate a new License Key.<br/><br/> ' .
                'Access to <a href="%urk" target="_blank">M2E Accounts</a> will allow you to manage your ' .
                'Subscription, keep track of your Trial and Paid terms, control your License Key details, and more.',
                [
                    'privacy' => \M2E\Core\Helper\Module\Support::WEBSITE_PRIVACY_URL,
                    'urk' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL,
                    'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                ]
            )
        );

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = $this->form->getUserForm();
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
