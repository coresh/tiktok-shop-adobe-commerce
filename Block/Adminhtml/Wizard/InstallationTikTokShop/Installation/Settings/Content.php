<?php

namespace M2E\TikTokShop\Block\Adminhtml\Wizard\InstallationTikTokShop\Installation\Settings;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;

class Content extends AbstractForm
{
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\ShippingProvider\Repository $shippingProviderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\ShippingProvider\Repository $shippingProviderRepository,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->accountRepository = $accountRepository;
        $this->shippingProviderRepository = $shippingProviderRepository;
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('wizardInstallationSettings');
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            __(
                'In this section, you can configure the general settings for the interaction ' .
                'between %extension_title and %channel_title Shops.<br><br>Anytime you can change these ' .
                'settings under <b>%channel_title > Configuration > General</b>.',
                [
                    'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle(),
                    'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle()
                ]
            )
        );

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $settings = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Settings\Tabs\Main::class);

        $settings->toHtml();
        $form = $settings->getForm();

        $this->addShippingMappingFieldset($form);

        $form->setData([
            'id' => 'edit_form',
            'method' => 'post',
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);
    }

    private function addShippingMappingFieldset(\Magento\Framework\Data\Form $form)
    {
        $account = $this->accountRepository->getFirst();

        $fieldset = $form->addFieldset(
            'shipping_mapping',
            [
                'legend' => __('Shipping Mapping'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'account_settings_account_id',
            'hidden',
            [
                'name' => 'account_settings[account_id]',
                'value' => $account->getId(),
            ]
        );

        $shippingMappingField = $fieldset->addField(
            'shipping_provider_mapping',
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Form\Element\ShippingProviderMapping::class,
            [
                'account' => $account,
                'exist_shipping_provider_mapping' => [],
            ]
        );

        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Form\Render $render */
        $render = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Form\Render::class);
        $shippingMappingField->setRenderer($render);
    }
}
