<?php

namespace M2E\TikTokShop\Block\Adminhtml\HealthStatus\Tabs;

use M2E\TikTokShop\Model\HealthStatus\Notification\Settings;
use M2E\TikTokShop\Model\HealthStatus\Task\Result;

class Notifications extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var \Magento\Backend\Model\Auth */
    private $auth;
    /** @var \M2E\TikTokShop\Model\HealthStatus\Notification\Settings */
    private Settings $notificationSettings;

    public function __construct(
        \Magento\Backend\Model\Auth $auth,
        \M2E\TikTokShop\Model\HealthStatus\Notification\Settings $notificationSettings,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->auth = $auth;
        $this->notificationSettings = $notificationSettings;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post',
                ],
            ]
        );

        $form->addField(
            'health_status_notification_help_block',
            self::HELP_BLOCK,
            [
                'content' => __('You can specify how M2E TikTok Shop Connect should notify you about Health Status of ' .
                    'your M2E TikTok Shop Connect by selecting: <ul>' .
                    '<li><b>Do Not Notify</b> - no notification required;</li>' .
                    '<li><b>On each Extension Page (default)</b> - notification will be shown on each ' .
                    'page of M2E TikTok Shop Connect Module;</li>' .
                    '<li><b>On each Magento Page</b> - notification will be shown on each page of Magento;</li>' .
                    '<li><b>As Magento System Notification</b> - notification will be shown via ' .
                    'Magento global messages system;</li>' .
                    '<li><b>Send me an eMail</b> - notification will be sent you to the provided email.</li></ul>' .
                    'Also, you can select a minimal Notifications Level: <ul>' .
                    '<li><b>Critical/Error (default)</b> - notification will arise only for critical ' .
                    'issue and error;</li>' .
                    '<li><b>Warning</b> - notification will arise once the error or warning occur;</li>' .
                    '<li><b>Notice</b> - notification will arise in case the error, warning or notice occur.</li>' .
                    '</ul>'),
            ]
        );

        $fieldSet = $form->addFieldset(
            'notification_field_set',
            ['legend' => false, 'collabsable' => false]
        );

        $fieldSet->addField(
            'notification_mode',
            self::SELECT,
            [
                'name' => 'notification_mode',
                'label' => __('Notify Me'),
                'values' => [
                    [
                        'value' => Settings::MODE_DISABLED,
                        'label' => __('Do Not Notify'),
                    ],
                    [
                        'value' => Settings::MODE_EXTENSION_PAGES,
                        'label' => __('On each Extension Page'),
                    ],
                    [
                        'value' => Settings::MODE_MAGENTO_PAGES,
                        'label' => __('On each Magento Page'),
                    ],
                    [
                        'value' => Settings::MODE_MAGENTO_SYSTEM_NOTIFICATION,
                        'label' => __('As Magento System Notification'),
                    ],
                    [
                        'value' => Settings::MODE_EMAIL,
                        'label' => __('Send me an Email'),
                    ],
                ],
                'value' => $this->notificationSettings->getMode(),
            ]
        );

        $email = $this->notificationSettings->getEmail();
        empty($email) && $email = $this->auth->getUser()->getEmail();

        $fieldSet->addField(
            'notification_email',
            'text',
            [
                'container_id' => 'notification_email_value_container',
                'name' => 'notification_email',
                'label' => __('Email'),
                'value' => $email,
                'class' => 'TikTokShop-validate-email',
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'notification_level',
            self::SELECT,
            [
                'name' => 'notification_level',
                'label' => __('Notification Level'),
                'values' => [
                    [
                        'value' => Result::STATE_CRITICAL,
                        'label' => __('Critical / Error'),
                    ],
                    [
                        'value' => Result::STATE_WARNING,
                        'label' => __('Warning'),
                    ],
                    [
                        'value' => Result::STATE_NOTICE,
                        'label' => __('Notice'),
                    ],
                ],
                'value' => $this->notificationSettings->getLevel(),
            ]
        );
        //------------------------------------

        $button = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Magento\Button::class,
            '',
            [
                'data' => [
                    'id' => 'submit_button',
                    'label' => __('Save'),
                    'onclick' => 'HealthStatusObj.saveClick()',
                    'class' => 'action-primary',
                ],
            ]
        );

        $fieldSet->addField(
            'submit_button_container',
            self::CUSTOM_CONTAINER,
            [
                'text' => $button->toHtml(),
            ]
        );
        //------------------------------------

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->add($this->getUrl('*/*/save'), 'formSubmit');

        $this->jsPhp->addConstants(
            [
                '\M2E\TikTokShop\Model\HealthStatus\Notification\Settings::MODE_EMAIL' => \M2E\TikTokShop\Model\HealthStatus\Notification\Settings::MODE_EMAIL,
            ]
        );

        $this->js->addRequireJs(
            ['hS' => 'TikTokShop/HealthStatus'],
            <<<JS

        window.HealthStatusObj = new HealthStatus();
JS
        );

        return parent::_beforeToHtml();
    }
}
