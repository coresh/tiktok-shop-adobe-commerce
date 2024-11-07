<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Processing;

class InitiatorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createByCreateDate(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ): Initiator {
        return $this->create($account, $shop, null);
    }

    public function createByUpdateDate(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        \DateTimeInterface $fromDate
    ): Initiator {
        return $this->create($account, $shop, $fromDate);
    }

    private function create(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop,
        ?\DateTimeInterface $fromDate
    ): Initiator {
        return $this->objectManager->create(
            Initiator::class,
            [
                'account' => $account,
                'shop' => $shop,
                'fromDate' => $fromDate,
            ],
        );
    }
}
