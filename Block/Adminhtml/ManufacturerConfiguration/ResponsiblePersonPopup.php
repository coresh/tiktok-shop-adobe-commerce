<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration;

class ResponsiblePersonPopup extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\TikTokShop\Model\Account $account;
    private ?\M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Model\Account $account,
        ?\M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->account = $account;
        $this->responsiblePerson = $responsiblePerson;
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'responsible_person',
                ],
            ]
        );

        // ----------------------------------------
        $responsiblePersonData = $this->responsiblePerson ? [
            'responsible_person_id' => $this->responsiblePerson->id,
            'name' => $this->responsiblePerson->name,
            'email' => $this->responsiblePerson->email,
            'country_code' => $this->responsiblePerson->phoneCountryCode,
            'local_number' => $this->responsiblePerson->phoneLocalNumber,
            'address_line_1' => $this->responsiblePerson->streetAddressLine1,
            'postal_code' => $this->responsiblePerson->postalCode,
            'country' => $this->responsiblePerson->country,
        ] : [];
        $defaultData = $this->getDefaultData();
        $formData = array_merge($defaultData, $responsiblePersonData);
        // ----------------------------------------

        $form->addField(
            'account_id_hidden',
            'hidden',
            [
                'name' => 'responsible_person[account_id]',
                'value' => $this->account->getId(),
                'class' => 'account_id',
            ]
        );

        $form->addField(
            'responsible_person_id_hidden',
            'hidden',
            [
                'name' => 'responsible_person[responsible_person_id]',
                'value' => $formData['responsible_person_id'],
            ]
        );

        $fieldSet = $form->addFieldset('general', []);
        $fieldSet->addField(
            'name',
            'text',
            [
                'name' => 'responsible_person[name]',
                'label' => __('Responsible Person Name'),
                'required' => true,
                'value' => $formData['name'],
            ]
        );

        $fieldSet->addField(
            'email',
            'text',
            [
                'name' => 'responsible_person[email]',
                'label' => __('Email'),
                'class' => 'validate-email',
                'required' => true,
                'value' => $formData['email'],
            ]
        );

        $fieldSet = $form->addFieldset('address', []);

        $fieldSet->addField(
            'country_code',
            'text',
            [
                'name' => 'responsible_person[country_code]',
                'label' => __('Phone Number: Country Code'),
                'required' => true,
                'value' => $formData['country_code'],
            ]
        );

        $fieldSet->addField(
            'local_number',
            'text',
            [
                'name' => 'responsible_person[local_number]',
                'label' => __('Phone Number: Local Number'),
                'class' => 'validate-number',
                'required' => true,
                'value' => $formData['local_number'],
            ]
        );

        $fieldSet->addField(
            'address1',
            'text',
            [
                'name' => 'responsible_person[address_1]',
                'label' => __('Address Line'),
                'required' => true,
                'value' => $formData['address_line_1'],
            ]
        );

        $fieldSet->addField(
            'postal_code',
            'text',
            [
                'name' => 'responsible_person[postal_code]',
                'label' => __('Postal Code'),
                'required' => true,
                'value' => $formData['postal_code'],
            ]
        );

        $fieldSet->addField(
            'country',
            'text',
            [
                'name' => 'responsible_person[country]',
                'label' => __('Country'),
                'required' => true,
                'value' => $formData['country'],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    private function getDefaultData(): array
    {
        return [
            'responsible_person_id' => '',
            'name' => '',
            'email' => '',
            'country_code' => '',
            'local_number' => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'district' => '',
            'city' => '',
            'province' => '',
            'postal_code' => '',
            'country' => '',
        ];
    }
}
