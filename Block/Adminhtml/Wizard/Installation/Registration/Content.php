<?php

namespace M2E\TikTokShop\Block\Adminhtml\Wizard\Installation\Registration;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm;

abstract class Content extends AbstractForm
{
    /** @var \Magento\Backend\Model\Auth\Session */
    protected $authSession;

    private \M2E\TikTokShop\Model\Registration\UserInfo\Repository $userInfoRepository;

    /** @var \M2E\TikTokShop\Helper\Magento\Admin */
    protected $magentoAdminHelper;

    /** @var \M2E\TikTokShop\Helper\Module\License */
    private $helperModuleLicense;
    /** @var \M2E\TikTokShop\Helper\Magento */
    private $magentoHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Helper\Module\License $helperModuleLicense,
        \M2E\TikTokShop\Model\Registration\UserInfo\Repository $manager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \M2E\TikTokShop\Helper\Magento\Admin $magentoAdminHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->userInfoRepository = $manager;
        $this->authSession = $authSession;
        $this->magentoAdminHelper = $magentoAdminHelper;
        $this->helperModuleLicense = $helperModuleLicense;
        $this->magentoHelper = $magentoHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            __(
                'M2E TikTok Shop Connect requires activation for further work. ' .
                'To activate your installation, you should obtain a <strong>License Key</strong>. For more details, ' .
                'please read our <a href="%1" target="_blank">Privacy Policy</a>.<br/><br/> Fill out the form ' .
                'below with the required information. This information will be used to register you on ' .
                '<a href="%2" target="_blank">M2E Accounts</a> and auto-generate a new License Key.<br/><br/> ' .
                'Access to <a href="%2" target="_blank">M2E Accounts</a> will allow you to manage your ' .
                'Subscription, keep track of your Trial and Paid terms, control your License Key details, and more.',
                \M2E\TikTokShop\Helper\Module\Support::WEBSITE_PRIVACY_URL,
                \M2E\TikTokShop\Helper\Module\Support::ACCOUNTS_URL
            )
        );

        parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $countries = $this->magentoHelper->getCountries();
        unset($countries[0]);
        $this->setData('available_countries', $countries);
        // ---------------------------------------

        // ---------------------------------------
        $userInfo = $this->magentoAdminHelper->getCurrentInfo();
        // ---------------------------------------

        // ---------------------------------------
        $earlierFormData = [];

        if ($info = $this->userInfoRepository->get()) {
            $earlierFormData['email'] = $info->getEmail();
            $earlierFormData['first_name'] = $info->getFirstname();
            $earlierFormData['last_name'] = $info->getLastname();
            $earlierFormData['phone'] = $info->getPhone();
            $earlierFormData['country'] = $info->getCountry();
            $earlierFormData['city'] = $info->getCity();
            $earlierFormData['postal_code'] = $info->getPostalCode();
        }

        $userInfo = array_merge($userInfo, $earlierFormData);

        $this->setData('user_info', $userInfo);
        $this->setData(
            'isLicenseStepFinished',
            !empty($earlierFormData) && $this->helperModuleLicense->getKey()
        );

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
            ],
        ]);

        $fieldset = $form->addFieldset(
            'block_notice_wizard_installation_step_license',
            [
                'legend' => '',
            ]
        );

        $fieldset->addField(
            'form_email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'value' => $this->getUserInfoValue('email'),
                'class' => 'TikTokShop-validate-email validate-length maximum-length-80',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished'),
            ]
        );

        $fieldset->addField(
            'first_name',
            'text',
            [
                'name' => 'firstname',
                'label' => __('First Name'),
                'value' => $this->getUserInfoValue('firstname'),
                'class' => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished'),
            ]
        );

        $fieldset->addField(
            'last_name',
            'text',
            [
                'name' => 'lastname',
                'label' => __('Last Name'),
                'value' => $this->getUserInfoValue('lastname'),
                'class' => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished'),
            ]
        );

        $fieldset->addField(
            'phone',
            'text',
            [
                'name' => 'phone',
                'label' => __('Phone'),
                'value' => $this->getUserInfoValue('phone'),
                'class' => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished'),
            ]
        );

        $fieldset->addField(
            'country',
            'select',
            [
                'name' => 'country',
                'label' => __('Country'),
                'value' => $this->getUserInfoValue('country'),
                'class' => 'validate-length maximum-length-40',
                'values' => $this->getData('available_countries'),
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished'),
            ]
        );

        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'value' => $this->getUserInfoValue('city'),
                'class' => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished'),
            ]
        );

        $fieldset->addField(
            'postal_code',
            'text',
            [
                'name' => 'postal_code',
                'label' => __('Postal Code'),
                'value' => $this->getUserInfoValue('postal_code'),
                'class' => 'validate-length maximum-length-40',
                'required' => true,
                'disabled' => $this->getData('isLicenseStepFinished'),
            ]
        );

        if (!$this->getData('isLicenseStepFinished')) {
            $this->css->add(
                <<<CSS
.field-licence_agreement .admin__field {
    padding-top: 8px;
}
CSS
            );

            $privacyUrl = \M2E\TikTokShop\Helper\Module\Support::WEBSITE_PRIVACY_URL;
            $termsUrl = \M2E\TikTokShop\Helper\Module\Support::WEBSITE_TERMS_URL;

            $fieldset->addField(
                'licence_agreement',
                'checkbox',
                [
                    'name' => 'licence_agreement',
                    'class' => 'admin__control-checkbox',
                    'label' => __('Terms and Privacy'),
                    'checked' => false,
                    'value' => 1,
                    'required' => true,
                    'after_element_html' => __(
                        <<<HTML
&nbsp; I agree to  <a href="{$termsUrl}" target="_blank">terms</a> and
<a href="{$privacyUrl}" target="_blank">privacy policy</a>
HTML
                    ),
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getUserInfoValue($name, $type = 'input')
    {
        $info = $this->getData('user_info');

        if (!empty($info[$name])) {
            return $info[$name];
        }

        if ($type == 'input') {
            return '';
        }

        $notSelectedWord = __('not selected');

        return <<<HTML
<span style="font-style: italic; color: grey;">
    [{$notSelectedWord}]
</span>
HTML;
    }
}
