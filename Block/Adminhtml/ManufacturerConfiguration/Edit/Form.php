<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Edit;

use M2E\TikTokShop\Model\ManufacturerConfiguration;

class Form extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private ?ManufacturerConfiguration $manufacturerConfiguration;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\ManufacturerConfiguration\SuggestedManufacturerList $suggestedManufacturerList;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\ManufacturerConfiguration\SuggestedManufacturerList $suggestedManufacturerList,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        ?ManufacturerConfiguration $manufacturerConfiguration = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->manufacturerConfiguration = $manufacturerConfiguration;
        $this->accountRepository = $accountRepository;
        $this->suggestedManufacturerList = $suggestedManufacturerList;
    }

    protected function _prepareForm(): Form
    {
        $formData = $this->getFormData();

        $form = $this->_formFactory->create([
            'data' => ['id' => 'edit_form'],
        ]);

        $fieldset = $form->addFieldset(
            'magento_block_template_compliance_edit_form',
            [
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'id',
            'hidden',
            [
                'name' => 'id',
                'value' => $formData['id'] ?? '',
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'label' => __('Title'),
                'name' => 'title',
                'value' => $formData['title'] ?? '',
                'required' => true,
                'tooltip' => __('Use the title of one of the Magento product brands that matches ' .
                    'the selected manufacturer.')
            ]
        );

        $fieldset->addField(
            'account_id',
            'select',
            [
                'label' => __('Account'),
                'name' => 'account_id',
                'value' => $formData['account_id'] ?? '',
                'values' => $this->getAccountOptions(),
                'required' => true,
            ]
        );

        $style = empty($formData['manufacturer_id']) ? 'display: none;' : '';

        $fieldset->addField(
            'manufacturer_id',
            self::SELECT,
            [
                'name' => 'manufacturer_id',
                'label' => __('Manufacturer'),
                'title' => __('Manufacturer'),
                'required' => true,
                'style' => 'max-width: 30%;',
                'after_element_html' => $this->createButtonsBlock(
                    [
                        $this->getManufacturerLinksHtml(true),
                        $this->getManufacturerLinksHtml(false),
                        $this->getRefreshButtonHtml($style),
                    ],
                    $style
                ),
            ]
        );

        $render = $this
            ->getLayout()
            ->createBlock(Form\ComplianceResponsiblePerson\FormElementRender::class);

        $fieldset->addField(
            'responsible_person_ids',
            Form\ComplianceResponsiblePerson\FormElement::class,
            [
                'account_id' => $formData['account_id'] ?? null,
                'saved_responsible_person_ids' => $formData['responsible_person_ids'] ?? [],
            ]
        )->setRenderer($render);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getFormData(): array
    {
        if ($this->manufacturerConfiguration === null) {
            return [];
        }

        return [
            'id' => $this->manufacturerConfiguration->getId(),
            'title' => $this->manufacturerConfiguration->getTitle(),
            'account_id' => $this->manufacturerConfiguration->getAccountId(),
            'manufacturer_id' => $this->manufacturerConfiguration->getManufacturerId(),
            'responsible_person_ids' => $this->manufacturerConfiguration->getResponsiblePersonIds(),
        ];
    }

    protected function _toHtml()
    {
        $css = <<<CSS
.actions .action {
    padding-right: 10px;
    padding-left: 10px;
}
CSS;

        $this->css->add($css);

        $formData = $this->getFormData();
        $currentAccountId = $formData['account_id'] ?? null;
        $currentManufacturerId = $formData['manufacturer_id'] ?? null;
        $responsiblePersonIds = null;
        if (!empty($formData['responsible_person_ids'])) {
            $responsiblePersonIds = json_encode($formData['responsible_person_ids']);
        }

        $urlGetManufacturers = $this->getUrl('*/manufacturerConfiguration/manufacturerList');
        $urlGetManufacturerPopupHtml = $this->getUrl('*/manufacturerConfiguration/getManufacturerPopupHtml');
        $urlManufactureUpdate = $this->getUrl('*/manufacturerConfiguration/manufacturerUpdate');
        $urlGetResponsiblePersons = $this->getUrl('*/manufacturerConfiguration/responsiblePersonsList');
        $urlGetResponsiblePersonPopupHtml = $this->getUrl(
            '*/manufacturerConfiguration/getResponsiblePersonPopupHtml'
        );
        $urlGetResponsiblePersonUpdate = $this->getUrl('*/manufacturerConfiguration/responsiblePersonUpdate');

        $source = json_encode($this->suggestedManufacturerList->get());

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/ManufacturerConfiguration/Form',
        'TikTokShop/ManufacturerConfiguration/Suggest'
    ], function() {
        window.ManufacturerConfigurationFormObj = new ManufacturerConfigurationForm({
            accountId: '$currentAccountId',
            manufacturerId: '$currentManufacturerId',
            responsiblePersonIds: '$responsiblePersonIds',
            urlGetResponsiblePersons: '$urlGetResponsiblePersons',
            urlGetResponsiblePersonPopupHtml: '$urlGetResponsiblePersonPopupHtml',
            urlGetResponsiblePersonUpdate: '$urlGetResponsiblePersonUpdate',
            urlGetManufacturers: '$urlGetManufacturers',
            urlGetManufacturerPopupHtml: '$urlGetManufacturerPopupHtml',
            urlManufactureUpdate: '$urlManufactureUpdate'
        });

        new ManufacturerConfigurationTitleSuggest('title', $source)
    });
JS
        );

        return parent::_toHtml();
    }

    private function getAccountOptions(): array
    {
        $options = [];
        foreach ($this->accountRepository->getAll() as $account) {
            if (!$account->hasAnyEuShop()) {
                continue;
            }

            $options[] = [
                'value' => $account->getId(),
                'label' => $account->getTitle(),
            ];
        }

        return $options;
    }

    /**
     * @param string[] $actions
     */
    private function createButtonsBlock(array $actions, string $style): string
    {
        $formattedActions = [];
        foreach ($actions as $action) {
            $formattedActions[] = sprintf('<span class="action">%s</span>', $action);
        }

        return sprintf(
            '<span class="actions" style="%s">%s</span>',
            $style,
            implode(' ', $formattedActions)
        );
    }

    private function getRefreshButtonHtml(string $style): string
    {
        $data = [
            'id' => 'refresh_manufacturer',
            'label' => __('Refresh'),
            'onclick' => 'ManufacturerConfigurationFormObj.updateManufacturers(true);',
            'class' => 'refresh_status primary',
            'style' => $style,
        ];

        return $this->getLayout()
                    ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                    ->setData($data)
                    ->toHtml();
    }

    private function getManufacturerLinksHtml(bool $isNew): string
    {
        $label = $isNew ? (string)__('Add New') : __('View / Edit');
        $param = $isNew ? 'true' : 'false';

        return sprintf(
            '<a href="#" id="manufacturer_details" onclick="ManufacturerConfigurationFormObj.loadManufacturerPopup(%s);">%s</a>',
            $param,
            $label
        );
    }
}
