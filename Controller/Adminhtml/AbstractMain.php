<?php

namespace M2E\TikTokShop\Controller\Adminhtml;

use M2E\TikTokShop\Helper\Module;
use M2E\TikTokShop\Model\HealthStatus\Task\Result;

abstract class AbstractMain extends AbstractBase
{
    protected function preDispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (($preDispatchResult = parent::preDispatch($request)) !== true) {
            return $preDispatchResult;
        }

        $this->addNotificationMessages();

        if ($request->isGet() && !$request->isPost() && !$request->isXmlHttpRequest()) {
            /** @var \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper */
            $exceptionHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\Module\Exception::class);
            try {
                $this->_objectManager->get(\M2E\TikTokShop\Helper\Client::class)->updateLocationData(false);
            } catch (\Throwable $exception) {
                $exceptionHelper->process($exception);
            }

            try {
                /** @var \M2E\TikTokShop\Model\Servicing\Dispatcher $dispatcher */
                $dispatcher = $this->_objectManager->get(\M2E\TikTokShop\Model\Servicing\Dispatcher::class);
                $dispatcher->processFastTasks();
            } catch (\Throwable $exception) {
                $exceptionHelper->process($exception);
            }
        }

        return true;
    }

    //########################################

    protected function initResultPage(): void
    {
        parent::initResultPage();

        if ($this->isContentLocked()) {
            $this->resultPage->getLayout()->unsetChild('page.wrapper', 'page_content');
            $this->resultPage->getLayout()->unsetChild('header', 'header.inner.left');
            $this->resultPage->getLayout()->unsetChild('header', 'header.inner.right');
        }
    }

    //########################################

    protected function addLeft(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        if (
            $this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()
        ) {
            if ($this->isContentLocked()) {
                return $this;
            }
        }

        return parent::addLeft($block);
    }

    /**
     * @param \Magento\Framework\View\Element\AbstractBlock|\Magento\Framework\View\Element\BlockInterface $block
     *
     * @return $this|\M2E\TikTokShop\Controller\Adminhtml\AbstractBase|\Magento\Framework\App\ResponseInterface
     */
    protected function addContent(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        if (
            $this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()
        ) {
            if ($this->isContentLocked()) {
                return $this;
            }
        }

        if ($this->isContentLockedByWizard()) {
            return $this->getRedirectToWizard();
        }

        return parent::addContent($block);
    }

    // ---------------------------------------

    protected function beforeAddContentEvent()
    {
        $this->appendMSINotificationPopup();

        parent::beforeAddContentEvent();
    }

    protected function appendMSINotificationPopup()
    {
        if (!$this->_objectManager->get(\M2E\TikTokShop\Helper\Magento::class)->isMSISupportingVersion()) {
            return;
        }

        if (
            $this->_objectManager
                ->get(\M2E\TikTokShop\Model\Registry\Manager::class)
                ->getValue('/view/msi/popup/shown/')
        ) {
            return;
        }

        $block = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\MsiNotificationPopup::class);
        $this->getLayout()->setChild('js', $block->getNameInLayout(), '');
    }

    protected function getRedirectToWizard()
    {
        /** @var Module\Wizard $wizardHelper */
        $wizardHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\Module\Wizard::class);
        $activeWizard = $wizardHelper->getActiveBlockerWizard($this->getCustomViewNick());
        $activeWizardNick = $wizardHelper->getNick($activeWizard);

        return $this->_redirect('*/wizard_' . $activeWizardNick, ['referrer' => $this->getCustomViewNick()]);
    }

    //########################################

    protected function getCustomViewHelper(): \M2E\TikTokShop\Helper\View\TikTokShop
    {
        return $this->getViewHelper()->getViewHelper();
    }

    protected function getCustomViewControllerHelper(): \M2E\TikTokShop\Helper\View\TikTokShop\Controller
    {
        return $this->getViewHelper()->getControllerHelper();
    }

    protected function getCustomViewNick()
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::NICK;
    }

    private function addNotificationMessages(): void
    {
        if (
            $this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()
        ) {
            $this->addHealthStatusNotifications();
            $this->addLicenseNotifications();

            if (!$this->addStaticContentNotification()) {
                $this->addStaticContentWarningNotification();
            }

            /** @var \M2E\TikTokShop\Helper\Module $moduleHelper */
            $moduleHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\Module::class);
            $this->addNotifications($moduleHelper->getUpgradeMessages());

            $this->addCronErrorMessage();
            $this->getCustomViewControllerHelper()->addMessages();
        }
    }

    // ---------------------------------------

    private function addStaticContentNotification(): bool
    {
        /** @var \M2E\TikTokShop\Helper\Magento $magentoHelper */
        $magentoHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\Magento::class);
        if (!$magentoHelper->isProduction()) {
            return false;
        }

        /** @var \M2E\TikTokShop\Helper\Module $moduleHelper */
        $moduleHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\Module::class);
        if (!$moduleHelper->isStaticContentDeployed()) {
            $this->addExtendedErrorMessage(
                __(
                    '<p>M2E TikTok Shop Connect interface cannot work properly and there is no way to ' .
                    'work with it correctly, as your Magento is set to the Production Mode and the static ' .
                    'content data was not deployed.</p>'
                ),
                self::GLOBAL_MESSAGES_GROUP
            );

            return true;
        }

        return false;
    }

    private function addStaticContentWarningNotification(): void
    {
        /** @var \M2E\TikTokShop\Helper\Magento $magentoHelper */
        $magentoHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\Magento::class);
        if (!$magentoHelper->isProduction()) {
            return;
        }

        /** @var \M2E\TikTokShop\Model\Module $moduleModel */
        $moduleModel = $this->_objectManager->get(\M2E\TikTokShop\Model\Module::class);
        $skipMessageForVersion = $this->_objectManager->get(\M2E\TikTokShop\Model\Registry\Manager::class)->getValue(
            '/global/notification/static_content/skip_for_version/'
        );

        if (
            $skipMessageForVersion !== null
            && version_compare($skipMessageForVersion, $moduleModel->getPublicVersion(), '==')
        ) {
            return;
        }

        $deployDate = $magentoHelper->getLastStaticContentDeployDate();
        if (!$deployDate) {
            return;
        }

        /** @var \M2E\Core\Model\Setup\Repository $setupResource */
        $setupResource = $this->_objectManager->get(\M2E\Core\Model\Setup\Repository::class);
        $lastUpgrade = $setupResource->findLastUpgrade(\M2E\TikTokShop\Helper\Module::IDENTIFIER);
        if ($lastUpgrade === null) {
            return;
        }

        $lastUpgradeDate = $lastUpgrade->getCreateDate();
        $deployDate = \M2E\TikTokShop\Helper\Date::createDateGmt($deployDate);

        if ($deployDate->getTimestamp() > $lastUpgradeDate->modify('- 30 minutes')->getTimestamp()) {
            return;
        }

        $this->addExtendedWarningMessage(
            __(
                '<p>Static content data was not deployed during the last M2E TikTok Shop Connect ' .
                'installation/upgrade. It may affect some elements of your Magento user interface.</p>' .
                '<p>Please follow <a href="%1" target="_blank">these instructions</a> to deploy ' .
                'static view files.</p><a href="%2">Don\'t Show Again</a><br>',
                'https://devdocs.magento.com/guides/v2.3/config-guide/cli/config-cli-subcommands-static-view.html',
                $this->getUrl(
                    '*/general/skipStaticContentValidationMessage',
                    [
                        'skip_message' => true,
                        'back' => base64_encode($this->getUrl('*/*/*', ['_current' => true])),
                    ]
                )
            ),
            self::GLOBAL_MESSAGES_GROUP
        );
    }

    private function addHealthStatusNotifications(): void
    {
        /** @var \M2E\TikTokShop\Model\HealthStatus\CurrentStatus $currentStatus */
        $currentStatus = $this->_objectManager->get(\M2E\TikTokShop\Model\HealthStatus\CurrentStatus::class);
        /** @var \M2E\TikTokShop\Model\HealthStatus\Notification\Settings $notificationSettings */
        $notificationSettings = $this->_objectManager->get(
            \M2E\TikTokShop\Model\HealthStatus\Notification\Settings::class
        );

        if (!$notificationSettings->isModeExtensionPages()) {
            return;
        }

        if ($currentStatus->get() < $notificationSettings->getLevel()) {
            return;
        }

        /** @var \M2E\TikTokShop\Model\HealthStatus\Notification\MessageBuilder $messageBuilder */
        $messageBuilder = $this->_objectManager->get(
            \M2E\TikTokShop\Model\HealthStatus\Notification\MessageBuilder::class
        );

        switch ($currentStatus->get()) {
            case Result::STATE_NOTICE:
                $this->addExtendedNoticeMessage($messageBuilder->build(), self::GLOBAL_MESSAGES_GROUP);
                break;

            case Result::STATE_WARNING:
                $this->addExtendedWarningMessage($messageBuilder->build(), self::GLOBAL_MESSAGES_GROUP);
                break;

            case Result::STATE_CRITICAL:
                $this->addExtendedErrorMessage($messageBuilder->build(), self::GLOBAL_MESSAGES_GROUP);
                break;
        }
    }

    protected function addLicenseNotifications(): void
    {
        $added = false;
        if ($this->getCustomViewHelper()->isInstallationWizardFinished()) {
            $added = $this->addLicenseActivationNotifications();
        }

        /** @var \M2E\Core\Model\LicenseService $licenseService */
        $licenseService = $this->_objectManager->get(\M2E\Core\Model\LicenseService::class);
        if (!$added && $licenseService->has()) {
            $this->addLicenseValidationFailNotifications();
        }
    }

    // ---------------------------------------

    /**
     * @param array $messages
     */
    protected function addNotifications(array $messages): void
    {
        foreach ($messages as $message) {
            if (isset($message['text']) && isset($message['type']) && $message['text'] != '') {
                switch ($message['type']) {
                    case \M2E\TikTokShop\Helper\Module::MESSAGE_TYPE_ERROR:
                        $this->getMessageManager()->addError(
                            $this->prepareNotificationMessage($message),
                            self::GLOBAL_MESSAGES_GROUP
                        );
                        break;
                    case \M2E\TikTokShop\Helper\Module::MESSAGE_TYPE_WARNING:
                        $this->getMessageManager()->addWarning(
                            $this->prepareNotificationMessage($message),
                            self::GLOBAL_MESSAGES_GROUP
                        );
                        break;
                    case \M2E\TikTokShop\Helper\Module::MESSAGE_TYPE_SUCCESS:
                        $this->getMessageManager()->addSuccess(
                            $this->prepareNotificationMessage($message),
                            self::GLOBAL_MESSAGES_GROUP
                        );
                        break;
                    case \M2E\TikTokShop\Helper\Module::MESSAGE_TYPE_NOTICE:
                    default:
                        $this->getMessageManager()->addNotice(
                            $this->prepareNotificationMessage($message),
                            self::GLOBAL_MESSAGES_GROUP
                        );
                        break;
                }
            }
        }
    }

    protected function prepareNotificationMessage(array $message)
    {
        if (!empty($message['title'])) {
            $title = __($message['title']);
            $text = __($message['text']);

            return "<strong>$title</strong><br/>$text";
        }

        return __($message['text']);
    }

    // ---------------------------------------

    protected function addCronErrorMessage(): void
    {
        /** @var \M2E\TikTokShop\Helper\Module $moduleHelper */
        $moduleHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\Module::class);
        /** @var \M2E\TikTokShop\Model\Cron\Config $cronConfig */
        $cronConfig = $this->_objectManager->get(\M2E\TikTokShop\Model\Cron\Config::class);

        if (!$cronConfig->isEnabled()) {
            $this->getMessageManager()->addWarning(
                __(
                    'Automatic Synchronization is disabled. You can enable it under ' .
                    '<i>Stores > Settings > Configuration > M2E TikTok Shop Connect > Module & Channels > ' .
                    'Automatic Synchronization</i>.'
                ),
                \M2E\TikTokShop\Controller\Adminhtml\AbstractBase::GLOBAL_MESSAGES_GROUP
            );

            return;
        }

        /** @var \M2E\TikTokShop\Model\Cron\Manager $cronManager */
        $cronManager = $this->_objectManager->get(\M2E\TikTokShop\Model\Cron\Manager::class);
        if (
            $moduleHelper->isReadyToWork()
            && $cronManager->isCronLastRunMoreThan(3600)
        ) {
            $message = __(
                'Attention! AUTOMATIC Synchronization is not running at the moment.' .
                ' It does not allow M2E TikTok Shop Connect to work correctly.' .
                '<br/>Please check this <a href="%1" target="_blank" class="external-link">article</a>' .
                ' for the details on how to resolve the problem.',
                'https://help.m2epro.com/support/solutions/articles/9000200402'
            );

            $this->getMessageManager()->addError(
                $message,
                \M2E\TikTokShop\Controller\Adminhtml\AbstractBase::GLOBAL_MESSAGES_GROUP
            );
        }
    }

    // ---------------------------------------

    protected function addLicenseActivationNotifications(): bool
    {
        /** @var \M2E\Core\Model\LicenseService $licenseService */
        $licenseService = $this->_objectManager->get(\M2E\Core\Model\LicenseService::class);

        if (
            !$licenseService->get()->getKey()
            || !$licenseService->get()->getInfo()->getDomainIdentifier()->getValidValue()
            || !$licenseService->get()->getInfo()->getIpIdentifier()->getValidValue()
        ) {
            $params = [];
            if ($this->isContentLockedByWizard()) {
                $params['wizard'] = '1';
            }

            /** @var \M2E\TikTokShop\Helper\View\Configuration $configurationHelper */
            $configurationHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\View\Configuration::class);

            $url = $configurationHelper->getLicenseUrl($params);

            $message = __(
                'M2E TikTok Shop Connect Module requires activation. Go to the <a href="%1" target ="_blank">License Page</a>.',
                $url
            );

            $this->getMessageManager()->addError($message, self::GLOBAL_MESSAGES_GROUP);

            return true;
        }

        return false;
    }

    protected function addLicenseValidationFailNotifications(): void
    {
        /** @var \M2E\Core\Model\LicenseService $licenseService */
        $licenseService = $this->_objectManager->get(\M2E\Core\Model\LicenseService::class);

        /** @var \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper */
        $wizardHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\Module\Wizard::class);
        /** @var \M2E\TikTokShop\Helper\View\Configuration $configurationHelper */
        $configurationHelper = $this->_objectManager->get(\M2E\TikTokShop\Helper\View\Configuration::class);
        if (!$licenseService->get()->getInfo()->getDomainIdentifier()->isValid()) {
            $params = [];
            if ($wizardHelper->getActiveBlockerWizard($this->getCustomViewNick())) {
                $params['wizard'] = '1';
            }

            $url = $configurationHelper->getLicenseUrl($params);

            $message = __(
                'M2E TikTok Shop Connect License Key Validation is failed for this Domain. ' .
                'Go to the <a href="%1" target="_blank">License Page</a>.',
                $url
            );

            $this->getMessageManager()->addError($message, self::GLOBAL_MESSAGES_GROUP);

            return;
        }

        if (!$licenseService->get()->getInfo()->getIpIdentifier()->isValid()) {
            $params = [];
            if ($wizardHelper->getActiveBlockerWizard($this->getCustomViewNick())) {
                $params['wizard'] = '1';
            }
            $url = $configurationHelper->getLicenseUrl($params);

            $message = __(
                'M2E TikTok Shop Connect License Key Validation is failed for this IP. ' .
                'Go to the <a href="%1" target="_blank">License Page</a>.',
                $url
            );

            $this->getMessageManager()->addError($message, self::GLOBAL_MESSAGES_GROUP);
        }
    }

    //########################################

    private function isContentLocked(): bool
    {
        return $this->_objectManager->get(\M2E\TikTokShop\Helper\Magento::class)->isProduction() &&
            !$this->_objectManager->get(\M2E\TikTokShop\Helper\Module::class)->isStaticContentDeployed();
    }

    private function isContentLockedByWizard(): bool
    {
        if ($this->isAjax()) {
            return false;
        }

        return false;
    }
}
