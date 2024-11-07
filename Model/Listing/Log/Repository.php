<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Log;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Listing\Log $resource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Listing\Log $resource
    ) {
        $this->resource = $resource;
    }

    public function create(\M2E\TikTokShop\Model\Listing\Log $log): void
    {
        $log->save();
    }

    public function updateProductTitle(int $productId, string $title): void
    {
        if (empty($title)) {
            return;
        }

        $this->resource
            ->getConnection()
            ->update(
                $this->resource->getMainTable(),
                ['product_title' => $title],
                ['product_id = ?' => $productId],
            );
    }

    public function removeForListing(int $listingId): void
    {
        $this->resource
            ->getConnection()
            ->delete(
                $this->resource->getMainTable(),
                [sprintf('`%s` = ?', \M2E\TikTokShop\Model\ResourceModel\Listing\Log::COLUMN_LISTING_ID) => $listingId],
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

    public function removeByAccountId(int $accountId): void
    {
        $this->resource
            ->getConnection()
            ->delete(
                $this->resource->getMainTable(),
                ['account_id = ?' => $accountId],
            );
    }
}
