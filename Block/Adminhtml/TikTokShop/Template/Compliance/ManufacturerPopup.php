<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Compliance;

class ManufacturerPopup extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private ?\M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer;
    private \M2E\TikTokShop\Model\Account $account;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Model\Account $account,
        ?\M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->account = $account;
        $this->manufacturer = $manufacturer;
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'manufacturer',
                ],
            ]
        );

        // ----------------------------------------
        $manufacturerData = $this->manufacturer ? [
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $this->manufacturer->name,
            'registered_trade_name' => $this->manufacturer->registeredTradeName,
            'email' => $this->manufacturer->email,
            'country_code' => $this->manufacturer->phoneCountryCode,
            'local_number' => $this->manufacturer->phoneLocalNumber,
            'address' => $this->manufacturer->address,
        ] : [];
        $defaultData = $this->getDefaultData();
        $formData = array_merge($defaultData, $manufacturerData);
        // ----------------------------------------

        $form->addField(
            'account_id_hidden',
            'hidden',
            [
                'name' => 'manufacturer[account_id]',
                'value' => $this->account->getId(),
                'class' => 'account_id',
            ]
        );

        $form->addField(
            'manufacturer_id_hidden',
            'hidden',
            [
                'name' => 'manufacturer[manufacturer_id]',
                'value' => $formData['manufacturer_id'],
            ]
        );

        $fieldSet = $form->addFieldset('general', []);
        $fieldSet->addField(
            'name',
            'text',
            [
                'name' => 'manufacturer[name]',
                'label' => __('Manufacturer Name'),
                'required' => true,
                'value' => $formData['name'],
            ]
        );

        $fieldSet->addField(
            'registered_trade_name',
            'text',
            [
                'name' => 'manufacturer[registered_trade_name]',
                'label' => __('Registered Trade Name'),
                'required' => false,
                'value' => $formData['registered_trade_name'],
            ]
        );

        $fieldSet->addField(
            'email',
            'text',
            [
                'name' => 'manufacturer[email]',
                'label' => __('Email'),
                'class' => 'validate-email',
                'required' => true,
                'value' => $formData['email'],
            ]
        );

        $fieldSet = $form->addFieldset('phone_info', []);

        $fieldSet->addField(
            'country_code',
            'text',
            [
                'name' => 'manufacturer[country_code]',
                'label' => __('Phone Number: Country Code'),
                'required' => true,
                'value' => $formData['country_code'],
            ]
        );

        $fieldSet->addField(
            'local_number',
            'text',
            [
                'name' => 'manufacturer[local_number]',
                'label' => __('Phone Number: Local Number'),
                'class' => 'validate-number',
                'required' => true,
                'value' => $formData['local_number'],
            ]
        );

        $fieldSet->addField(
            'address',
            'text',
            [
                'name' => 'manufacturer[address]',
                'label' => __('Address'),
                'required' => true,
                'value' => $formData['address'],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    private function getDefaultData(): array
    {
        return [
            'manufacturer_id' => '',
            'name' => '',
            'registered_trade_name' => '',
            'email' => '',
            'country_code' => '',
            'local_number' => '',
            'address' => '',
        ];
    }
}
