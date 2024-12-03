<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Edit;

class Form extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalDataHelper;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->accountRepository = $accountRepository;
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

        $templateNick = $this->getTemplateNick();

        if ($templateNick == \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_COMPLIANCE) {
            $form->addField(
                'template_compliance_form_help_info',
                self::HELP_BLOCK,
                [
                    'content' => __(
                        'To comply with EU regulations, please provide the Manufacturer and Responsible Person
                     details for your products. The Manufacturer is the entity that produces the product, while
                     the Responsible Person is the individual or company within the EU responsible for product
                     compliance and safety. With the settings below, you may select a manufacturer and responsible
                     person already created on TikTok Shop or create a new one.'
                    ),
                ]
            );
        }

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

        if ($templateNick === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_COMPLIANCE) {
            if ($this->getRequest()->getParam('account_id', false) !== false) {
                $fieldset->addField(
                    'account_id_hidden',
                    'hidden',
                    [
                        'name' => 'compliance[account_id]',
                        'value' => $templateData['account_id'],
                    ]
                );
            }

            $fieldset->addField(
                'account_id',
                'select',
                [
                    'name' => 'compliance[account_id]',
                    'label' => __('Account'),
                    'title' => __('Account'),
                    'values' => $this->getAccountIdOptionsForEU(),
                    'value' => $templateData['account_id'],
                    'required' => true,
                    'disabled' => !empty($templateData['account_id']),
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    public function getTemplateData()
    {
        $accountId = $this->getRequest()->getParam('account_id', false);

        $nick = $this->getTemplateNick();
        $templateData = $this->globalDataHelper->getValue("tiktokshop_template_$nick");

        return array_merge([
            'title' => '',
            'account_id' => ($accountId !== false) ? $accountId : '',
        ], $templateData->getData());
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Edit
     */
    public function getParentBlock()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Edit */
        return parent::getParentBlock();
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

    private function getAccountOptions(): array
    {
        return $this->formatAccountOptions($this->accountRepository->getAll());
    }

    private function getAccountIdOptionsForEU(): array
    {
        return $this->formatAccountOptions($this->accountRepository->findWithEUShop());
    }

    private function formatAccountOptions(array $accounts): array
    {
        $optionsResult = [
            ['value' => '', 'label' => ''],
        ];
        foreach ($accounts as $account) {
            $optionsResult[] = [
                'value' => $account->getId(),
                'label' => $account->getTitle(),
            ];
        }

        return $optionsResult;
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
