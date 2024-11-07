<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order\UploadByUser;

class Configure extends \M2E\TikTokShop\Controller\Adminhtml\AbstractOrder
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

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        if (empty($accountId)) {
            $this->getErrorJsonResponse(__('Account id not set.'));
        }

        $account = $this->accountRepository->find((int)$accountId);

        if ($account === null) {
            return $this->getErrorJsonResponse(__('Not found Account.'));
        }

        $from = $this->getRequest()->getParam('from_date');
        if (empty($from)) {
            return $this->getErrorJsonResponse(__('From date not set.'));
        }

        $to = $this->getRequest()->getParam('to_date');
        if (empty($to)) {
            return $this->getErrorJsonResponse(__('To date not set.'));
        }

        $manager = $this->getManager($account);
        $fromDate = \M2E\TikTokShop\Helper\Date::timezoneDateToGmt($from);
        $toDate = \M2E\TikTokShop\Helper\Date::timezoneDateToGmt($to);

        if ($this->isMoreThanCurrentDate($toDate)) {
            $toDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
        }

        try {
            $manager->setFromToDates($fromDate, $toDate);
        } catch (\Throwable $exception) {
            return $this->getErrorJsonResponse($exception->getMessage());
        }

        $this->setJsonContent(['result' => true]);

        return $this->getResult();
    }

    // ---------------------------------------

    /**
     * @throws \Exception
     */
    protected function isMoreThanCurrentDate(\DateTime $toDate): bool
    {
        $nowTimestamp = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->getTimestamp();

        if ($toDate->getTimestamp() > $nowTimestamp) {
            return true;
        }

        return false;
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
