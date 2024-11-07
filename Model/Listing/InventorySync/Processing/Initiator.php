<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Processing;

class Initiator implements \M2E\TikTokShop\Model\Processing\PartialInitiatorInterface
{
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\Shop $shop;
    private \M2E\TikTokShop\Model\Listing\InventorySync\AccountLockManager $accountLockManager;
    private ?\DateTimeInterface $fromDate;

    public function __construct(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\Listing\InventorySync\AccountLockManager $accountLockManager,
        ?\DateTimeInterface $fromDate = null
    ) {
        $this->account = $account;
        $this->shop = $shop;
        $this->accountLockManager = $accountLockManager;
        $this->fromDate = $fromDate;
    }

    public function getInitCommand(): \M2E\TikTokShop\Model\Connector\CommandProcessingInterface
    {
        if ($this->fromDate === null) {
            return new Connector\InventoryGetItemsCommand(
                $this->account->getServerHash(),
                $this->shop->getShopId(),
            );
        }

        return new Connector\InventoryGetItemsByUpdateDateCommand(
            $this->account->getServerHash(),
            $this->shop->getShopId(),
            $this->fromDate
        );
    }

    public function generateProcessParams(): array
    {
        return [
            'account_id' => $this->account->getId(),
            'shop_id' => $this->shop->getId(),
            'from_date' => $this->fromDate === null ? null : $this->fromDate->format('Y-m-d H:i:s'),
            'current_date' => \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'),
        ];
    }

    public function getResultHandlerNick(): string
    {
        return ResultHandler::NICK;
    }

    public function initLock(\M2E\TikTokShop\Model\Processing\LockManager $lockManager): void
    {
        $lockManager->create(\M2E\TikTokShop\Model\Shop::LOCK_NICK, $this->shop->getId());
        $this->accountLockManager->create($this->shop);
    }
}
