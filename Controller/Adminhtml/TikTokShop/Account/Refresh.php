<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Account;

class Refresh extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount
{
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Account\Update $updateAccountModel;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Account\Update $updateAccountModel
    ) {
        parent::__construct();
        $this->accountRepository = $accountRepository;
        $this->updateAccountModel = $updateAccountModel;
    }

    public function execute()
    {
        try {
            $id = (int)$this->getRequest()->getParam('id');
            $account = $this->accountRepository->get($id);
            $this->updateAccountModel->refresh($account);

            $this->messageManager->addSuccessMessage(__('Account data has been refreshed.'));
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(__('The account data failed to be updated, please try to refresh it again.'));
        }

        return $this->_redirect('*/*/edit', ['id' => $id, 'trigger_validation' => true]);
    }
}
