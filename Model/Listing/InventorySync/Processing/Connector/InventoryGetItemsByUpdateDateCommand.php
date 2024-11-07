<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Processing\Connector;

class InventoryGetItemsByUpdateDateCommand extends InventoryGetItemsCommand
{
    private \DateTimeInterface $fromDate;

    public function __construct(string $accountServerHash, string $shopId, \DateTimeInterface $fromDate)
    {
        parent::__construct($accountServerHash, $shopId);

        $this->fromDate = $fromDate;
    }

    public function getRequestData(): array
    {
        $data = parent::getRequestData();

        $data['by_update'] = true;
        $data['from_date'] = $this->fromDate->format('Y-m-d H:i:s');

        return $data;
    }
}
