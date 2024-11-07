<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Account;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount;

class BeforeGetToken extends AbstractAccount
{
    private \M2E\TikTokShop\Helper\Module\Exception $helperException;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Account\GetGrantAccessUrl\Processor $connectProcessor;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Account\GetGrantAccessUrl\Processor $connectProcessor,
        \M2E\TikTokShop\Helper\Module\Exception $helperException
    ) {
        parent::__construct();

        $this->helperException = $helperException;
        $this->connectProcessor = $connectProcessor;
        $this->accountRepository = $accountRepository;
    }

    public function execute(): void
    {
        $accountId = (int)$this->getRequest()->getParam('id', 0);
        $region = $this->getRequest()->getParam('region');

        try {
            $backUrl = $this->getUrl('*/*/afterGetToken', [
                'id' => $accountId,
                'region' => $region,
                '_current' => true,
            ]);

            if ($accountId !== 0) {
                $account = $this->accountRepository->get($accountId);
                $response = $this->connectProcessor->processRefreshToken($backUrl, $account);
            } else {
                $response = $this->connectProcessor->processAddAccount($backUrl, $region);
            }
        } catch (\Exception $exception) {
            $this->helperException->process($exception);
            $error = __(
                'The TikTok Shop token obtaining is currently unavailable.<br/>Reason: %error_message',
                ['error_message' => $exception->getMessage()]
            );

            $this->messageManager->addError($error);

            $this->_redirect($this->getUrl('*/*/index'));

            return;
        }

        $this->_redirect($response->getUrl());
    }
}
