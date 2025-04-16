<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Account;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount;
use M2E\TikTokShop\Model\Account\Issue\ValidTokens;

class AfterGetToken extends AbstractAccount
{
    private \M2E\TikTokShop\Helper\Module\Exception $helperException;
    private \M2E\TikTokShop\Model\Account\Update $accountUpdate;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Account\Create $accountCreate;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Create $accountCreate,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Account\Update $accountUpdate,
        \M2E\TikTokShop\Helper\Module\Exception $helperException
    ) {
        parent::__construct();

        $this->helperException = $helperException;
        $this->accountUpdate = $accountUpdate;
        $this->accountRepository = $accountRepository;
        $this->accountCreate = $accountCreate;
    }

    // ----------------------------------------

    public function execute()
    {
        $authCode = $this->getRequest()->getParam('code');
        $region = $this->getRequest()->getParam('region');

        if ($authCode === null) {
            $this->_redirect('*/*/index');
        }

        $accountId = (int)$this->getRequest()->getParam('id');
        try {
            if (empty($accountId)) {
                $account = $this->accountCreate->create($authCode, $region);

                return $this->_redirect(
                    '*/*/edit',
                    [
                        'id' => $account->getId(),
                        '_current' => true,
                        'tab' => \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Tabs::TAB_ID_INVOICES_AND_SHIPMENTS,
                        'trigger_validation' => true
                    ],
                );
            }

            $account = $this->accountRepository->find($accountId);
            if ($account === null) {
                throw new \LogicException('Account not found.');
            }

            $this->accountUpdate->updateCredentials($account, $authCode);
        } catch (\Throwable $exception) {
            $this->helperException->process($exception);

            $this->messageManager->addError(
                __(
                    'The TikTokShop access obtaining is currently unavailable.<br/>Reason: %error_message',
                    ['error_message' => $exception->getMessage()],
                ),
            );

            return $this->_redirect('*/tiktokshop_account');
        }

        $this->messageManager->addSuccessMessage(__('Auth code was saved'));

        return $this->_redirect('*/*/edit', ['id' => $accountId, '_current' => true]);
    }
}
