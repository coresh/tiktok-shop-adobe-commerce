<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\General;

class GetAccounts extends \M2E\TikTokShop\Controller\Adminhtml\AbstractGeneral
{
    private \M2E\TikTokShop\Model\Account\Repository $accountsRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountsRepository
    ) {
        parent::__construct();

        $this->accountsRepository = $accountsRepository;
    }

    public function execute()
    {
        $accounts = [];
        foreach ($this->accountsRepository->getAll() as $account) {
            $accounts[] = [
                'id' => $account->getId(),
                'title' => \M2E\TikTokShop\Helper\Data::escapeHtml($account->getTitle()),
            ];
        }

        $this->setJsonContent($accounts);

        return $this->getResult();
    }
}
