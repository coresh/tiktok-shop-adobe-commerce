<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Compliance\Edit\Form;

class Data extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): Data
    {
        $formData = $this->getFormData();
        $default = $this->getDefault();
        $formData = array_merge($default, $formData);

        $form = $this->_formFactory->create();

        $form->addField(
            'compliance_id',
            'hidden',
            [
                'name' => 'compliance[id]',
                'value' => $formData['id'] ?? '',
            ]
        );

        $form->addField(
            'compliance_title',
            'hidden',
            [
                'name' => 'compliance[title]',
                'value' => $this->getTitle(),
            ]
        );

        $viewText = __('View');
        $editText = __('Edit');
        $orText = __('or');
        $addNewText = __('Add New');

        $fieldset = $form->addFieldset(
            'magento_block_template_compliance_edit_form',
            [
                'legend' => __('Product Compliance'),
                'collapsable' => false,
            ]
        );

        $style = empty($formData['manufacturer_id']) ? 'display: none;' : '';

        $fieldset->addField(
            'manufacturer_id',
            self::SELECT,
            [
                'name' => 'compliance[manufacturer_id]',
                'label' => __('Manufacturer'),
                'title' => __('Manufacturer'),
                'required' => true,
                'after_element_html' => $this->createButtonsBlock(
                    [
                        $this->getManufacturerLinksHtml(true),
                        $this->getManufacturerLinksHtml(false),
                        $this->getRefreshButtonHtml(
                            'refresh_manufacturer',
                            'TikTokShopTemplateComplianceObj.updateManufacturers(true);',
                            $style
                        ),
                    ],
                    $style
                ),
            ]
        );

        $style = empty($formData['responsible_person_id']) ? 'display: none;' : '';

        $fieldset->addField(
            'responsible_person_id',
            self::SELECT,
            [
                'name' => 'compliance[responsible_person_id]',
                'label' => __('Responsible Person'),
                'title' => __('Responsible Person'),
                'class' => 'admin__control-select',
                'required' => true,
                'after_element_html' => $this->createButtonsBlock(
                    [
                        $this->getResponsiblePersonLinksHtml(true),
                        $this->getResponsiblePersonLinksHtml(false),
                        $this->getRefreshButtonHtml(
                            'refresh_responsible_person',
                            'TikTokShopTemplateComplianceObj.updateResponsiblePersons(true);',
                            $style
                        ),
                    ],
                    $style
                ),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function getTitle()
    {
        $template = $this->globalDataHelper->getValue('tiktokshop_template_compliance');

        if ($template === null) {
            return '';
        }

        return $template->getTitle();
    }

    private function getFormData()
    {
        $template = $this->globalDataHelper->getValue('tiktokshop_template_compliance');

        if ($template === null || $template->getId() === null) {
            return [];
        }

        return $template->getData();
    }

    private function getDefault(): array
    {
        return [
            'manufacturer_id' => '',
            'responsible_person_id' => '',
        ];
    }

    protected function _toHtml()
    {
        $formData = $this->getFormData();
        $currentAccountId = $formData['account_id'] ?? null;
        $currentManufacturerId = $formData['manufacturer_id'] ?? null;
        $currentResponsiblePersonId = $formData['responsible_person_id'] ?? null;

        $urlGetManufacturers = $this->getUrl('*/tiktokshop_template_compliance/manufacturerList');
        $urlGetManufacturerPopupHtml = $this->getUrl('*/tiktokshop_template_compliance/getManufacturerPopupHtml');
        $urlManufactureUpdate = $this->getUrl('*/tiktokshop_template_compliance/manufacturerUpdate');
        $urlGetResponsiblePersons = $this->getUrl('*/tiktokshop_template_compliance/responsiblePersonsList');
        $urlGetResponsiblePersonPopupHtml = $this->getUrl(
            '*/tiktokshop_template_compliance/getResponsiblePersonPopupHtml'
        );
        $urlGetResponsiblePersonUpdate = $this->getUrl('*/tiktokshop_template_compliance/responsiblePersonUpdate');

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/TikTokShop/Template/Compliance'
        ], function() {
    window.TikTokShopTemplateComplianceObj = new TikTokShopTemplateCompliance({
            accountId: '$currentAccountId',
            manufacturerId: '$currentManufacturerId',
            responsiblePersonId: '$currentResponsiblePersonId',
            urlGetResponsiblePersons: '$urlGetResponsiblePersons',
            urlGetResponsiblePersonPopupHtml: '$urlGetResponsiblePersonPopupHtml',
            urlGetResponsiblePersonUpdate: '$urlGetResponsiblePersonUpdate',
            urlGetManufacturers: '$urlGetManufacturers',
            urlGetManufacturerPopupHtml: '$urlGetManufacturerPopupHtml',
            urlManufactureUpdate: '$urlManufactureUpdate'
        });
    });
JS
        );

        return parent::_toHtml();
    }

    /**
     * @param string[] $actions
     *
     * @return string
     */
    private function createButtonsBlock(array $actions, string $style): string
    {
        $formattedActions = [];
        /** @var string $action */
        foreach ($actions as $action) {
            $formattedActions[] = sprintf('<span class="action">%s</span>', $action);
        }

        return sprintf(
            '<span class="actions" style="%s">%s</span>',
            $style,
            implode(' ', $formattedActions)
        );
    }

    private function getRefreshButtonHtml(string $id, string $onClick, string $style): string
    {
        $data = [
            'id' => $id,
            'label' => __('Refresh'),
            'onclick' => $onClick,
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
            '<a href="#" id="manufacturer_details" onclick="TikTokShopTemplateComplianceObj.loadManufacturerPopup(%s);">%s</a>',
            $param,
            $label
        );
    }

    private function getResponsiblePersonLinksHtml(bool $isNew): string
    {
        $label = $isNew ? (string)__('Add New') : __('View / Edit');
        $param = $isNew ? 'true' : 'false';

        return sprintf(
            '<a href="#" id="manufacturer_details" onclick="TikTokShopTemplateComplianceObj.loadResponsiblePersonPopup(%s);">%s</a>',
            $param,
            $label
        );
    }
}
