<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Wizard\InstallationTikTokShop;

class AfterToken extends Installation
{
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\Account\Create $accountCreate;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Create $accountCreate,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Helper\Module\Wizard $wizardHelper,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        parent::__construct(
            $magentoHelper,
            $wizardHelper,
            $nameBuilder,
            $licenseService,
        );

        $this->exceptionHelper = $exceptionHelper;
        $this->accountCreate = $accountCreate;
    }

    public function execute(): \Magento\Framework\App\ResponseInterface
    {
        $authCode = $this->getRequest()->getParam('code');
        $region = $this->getRequest()->getParam('region');

        if (!$authCode) {
            $this->messageManager->addError(__('Auth Code is not defined'));

            return $this->_redirect('*/*/installation');
        }

        try {
            $this->accountCreate->create($authCode, $region);
            $this->setStep($this->getNextStep());
        } catch (\Throwable $throwable) {
            $this->exceptionHelper->process($throwable);
            $this->messageManager->addError(__('Account Add Entity failed.'));
        }

        return $this->_redirect('*/*/installation');
    }
}
