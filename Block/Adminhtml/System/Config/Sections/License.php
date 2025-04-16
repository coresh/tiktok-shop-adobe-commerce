<?php

namespace M2E\TikTokShop\Block\Adminhtml\System\Config\Sections;

class License extends \M2E\TikTokShop\Block\Adminhtml\System\Config\Sections
{
    private string $key;
    private array $licenseData;
    private \M2E\Core\Model\LicenseService $licenseService;
    private \M2E\Core\Helper\Client $clientHelper;

    public function __construct(
        \M2E\Core\Model\LicenseService $licenseService,
        \M2E\Core\Helper\Client $clientHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->licenseService = $licenseService;
        $this->clientHelper = $clientHelper;
    }

    protected function _prepareForm()
    {
        $this->prepareLicenseData();

        $form = $this->_formFactory->create();

        $form->addField(
            'block_notice_configuration_license',
            self::HELP_BLOCK,
            [
                'no_collapse' => true,
                'no_hide' => true,
                'content' => __(
                    '<p>To use %extension_title, you need to register on M2E Accounts ' .
                    'and generate a License Key.</p><br><p>Your email address used during the initial setup ' .
                    'of %extension_title automatically registers you on M2E Accounts. After logging in, you can ' .
                    'manage your Subscription and Billing information.</p><br><p>License Key is a unique ' .
                    'identifier of %extension_title instance which is generated automatically and strictly ' .
                    'associated with the current IP and Domain of your Magento.</p><br><p>The same License Key ' .
                    'cannot be used for different domains, sub-domains or IPs. If your Magento Server changes ' .
                    'its location, the new License Key must be obtained and provided to %extension_title License ' .
                    'section. Click <strong>Save</strong> after the changes are made.</p><br><p>' .
                    '<strong>Note:</strong> If you need some assistance to activate your %extension_title instance, ' .
                    'please contact Support Team at <a href="mailto:%email">%email</a>.</p>',
                    [
                        'email' => 'support@m2epro.com',
                        'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle(),
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                    ]
                ),
            ]
        );

        $fieldSet = $form->addFieldset(
            'magento_block_configuration_license_data',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ]
        );

        $fieldData = [
            'label' => __('License Key'),
            'text' => $this->key,
        ];

        $fieldSet->addField(
            'license_text_key_container',
            self::NOTE,
            $fieldData
        );

        if ($this->licenseData['info']['email'] != '') {
            $fieldSet->addField(
                'associated_email',
                self::NOTE,
                [
                    'label' => __('Associated Email'),
                    'text' => $this->licenseData['info']['email'],
                    'tooltip' => __(
                        'This email address is associated with your License. ' .
                        'You can also use it to access <a href="%url" target="_blank" ' .
                        'class="external-link">M2E Accounts</a>.',
                        ['url' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL],
                    ),
                ]
            );
        }

        if ($this->key != '') {
            $fieldSet->addField(
                'manage_license',
                self::LINK,
                [
                    'label' => '',
                    'value' => __('Manage License'),
                    'href' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL,
                    'class' => 'external-link',
                    'target' => '_blank',
                ]
            );
        }

        if ($this->licenseData['domain'] != '' || $this->licenseData['ip'] != '') {
            $fieldSet = $form->addFieldset(
                'magento_block_configuration_license_valid',
                [
                    'legend' => __('Valid Location'),
                    'collapsable' => false,
                ]
            );

            if ($this->licenseData['domain'] != '') {
                $text = '<span ' . ($this->licenseData['valid']['domain'] ? '' : 'style="color: red;"') . '>
                            ' . $this->licenseData['domain'] . '
                        </span>';
                if (
                    !$this->licenseData['valid']['domain']
                    && $this->licenseData['connection']['domain'] !== null
                ) {
                    $text .= sprintf(
                        '<span>(%s: %s)</span>',
                        __('Your Domain'),
                        \M2E\Core\Helper\Data::escapeHtml($this->licenseData['connection']['domain'])
                    );
                }

                $fieldSet->addField(
                    'domain_field',
                    self::NOTE,
                    [
                        'label' => __('Domain'),
                        'text' => $text,
                    ]
                );
            }

            if ($this->licenseData['ip'] != '') {
                $text = '<span ' . ($this->licenseData['valid']['ip'] ? '' : 'style="color: red;"') . '>
                            ' . $this->licenseData['ip'] . '
                        </span>';
                if (
                    !$this->licenseData['valid']['ip']
                    && $this->licenseData['connection']['ip'] !== null
                ) {
                    $text .= '<span> (' . __('Your IP') . ': '
                        . \M2E\Core\Helper\Data::escapeHtml($this->licenseData['connection']['ip']) . ')</span>';
                }

                $fieldSet->addField(
                    'ip_field',
                    self::NOTE,
                    [
                        'label' => __('IP(s)'),
                        'text' => $text,
                        'after_element_html' => $this->getChildHtml('refresh_status'),
                    ]
                );
            }
        }

        $fieldSet = $form->addFieldset(
            'magento_block_configuration_license',
            [
                'legend' => $this->key == ''
                    ? (string)__('General')
                    : (string)__('Additional'),
                'collapsable' => false,
            ]
        );

        $fieldSet->addField(
            'license_buttons',
            'note',
            [
                'text' => '<span style="padding-right: 10px;">' . $this->getChildHtml('new_license') . '</span>'
                    . '<span>' . $this->getChildHtml('change_license') . '</span>',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function prepareLicenseData()
    {
        $license = $this->licenseService->get();
        $domainIdentifier = $license->getInfo()->getDomainIdentifier();
        $ipIdentifier = $license->getInfo()->getIpIdentifier();

        $this->key = \M2E\Core\Helper\Data::escapeHtml($license->getKey());

        $this->licenseData = [
            'domain' => \M2E\Core\Helper\Data::escapeHtml($domainIdentifier->getValidValue()),
            'ip' => \M2E\Core\Helper\Data::escapeHtml($ipIdentifier->getValidValue()),
            'info' => [
                'email' => \M2E\Core\Helper\Data::escapeHtml($license->getInfo()->getEmail()),
            ],
            'valid' => [
                'domain' => $domainIdentifier->isValid(),
                'ip' => $ipIdentifier->isValid(),
            ],
            'connection' => [
                'domain' => $this->clientHelper->getDomain(),
                'ip' => $this->clientHelper->getIp(),
                'directory' => $this->clientHelper->getBaseDirectory(),
            ],
        ];

        $data = [
            'label' => __('Refresh'),
            'onclick' => 'LicenseObj.refreshStatus();',
            'class' => 'refresh_status primary',
            'style' => 'margin-left: 2rem',
        ];
        $buttonBlock = $this->getLayout()
                            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                            ->setData($data);
        $this->setChild('refresh_status', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = [
            'label' => $this->key == ''
                ? (string)__('Use Existing License')
                : (string)__('Change License'),
            'onclick' => 'LicenseObj.changeLicenseKeyPopup();',
            'class' => 'change_license primary',
        ];
        $buttonBlock = $this->getLayout()
                            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                            ->setData($data);
        $this->setChild('change_license', $buttonBlock);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        try {
            $this->clientHelper->updateLocationData(true);
            // @codingStandardsIgnoreLine
        } catch (\Throwable $exception) {
        }

        $this->jsTranslator->addTranslations(
            [
                'Use Existing License' => __('Use Existing License'),
                'Cancel' => __('Cancel'),
                'Confirm' => __('Confirm'),
                'Internal Server Error' => __('Internal Server Error'),
            ]
        );

        $this->jsUrl->add($this->getUrl('m2e_tiktokshop/settings_license/refreshStatus'), 'settings_license/refreshStatus');
        $this->jsUrl->add($this->getUrl('m2e_tiktokshop/settings_license/change'), 'settings_license/change');
        $this->jsUrl->add($this->getUrl('m2e_tiktokshop/settings_license/section'), 'settings_license/section');

        $this->js->addRequireJs(
            [
                'l' => 'TikTokShop/Settings/License',
            ],
            <<<JS
window.LicenseObj = new License();
JS
        );
    }
}
