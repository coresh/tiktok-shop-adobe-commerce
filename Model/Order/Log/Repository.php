<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Log;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Order\Log $resource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Order\Log $resource
    ) {
        $this->resource = $resource;
    }

    public function removeByAccountId(int $accountId): void
    {
        $this->resource
            ->getConnection()
            ->delete(
                $this->resource->getMainTable(),
                ['account_id = ?' => $accountId],
            );
    }

    public function remove(?\DateTime $borderDate): void
    {
        $condition = [];
        if ($borderDate !== null) {
            $condition = [
                ' `create_date` < ? OR `create_date` IS NULL ' => $borderDate->format('Y-m-d H:i:s'),
            ];
        }

        $this->resource
            ->getConnection()
            ->delete($this->resource->getMainTable(), $condition);
    }
}
