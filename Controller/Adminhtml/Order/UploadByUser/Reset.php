<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order\UploadByUser;

class Reset extends \M2E\TikTokShop\Controller\Adminhtml\AbstractOrder
{
    private \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\ManagerFactory $managerFactory;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\ManagerFactory $managerFactory,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->managerFactory = $managerFactory;
        $this->accountRepository = $accountRepository;
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        if (empty($accountId)) {
            return $this->getErrorJsonResponse(__('Account must be specified.'));
        }

        $account = $this->accountRepository->find((int)$accountId);

        if ($account === null) {
            return $this->getErrorJsonResponse(__('Not found Account.'));
        }

        $manager = $this->getManager($account);
        $manager->clear();

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }

    protected function getManager(
        \M2E\TikTokShop\Model\Account $account
    ): \M2E\TikTokShop\Model\Cron\Task\Order\UploadByUser\Manager {
        return $this->managerFactory->create($account);
    }

    private function getErrorJsonResponse(string $errorMessage)
    {
        $json = [
            'result' => false,
            'messages' => [
                [
                    'type' => 'error',
                    'text' => $errorMessage,
                ],
            ],
        ];
        $this->setJsonContent($json);

        return $this->getResult();
    }
}
