<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Log;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Listing\Log $resource;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\Log\CollectionFactory $listingLogCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Listing\Log                   $resource,
        \M2E\TikTokShop\Model\ResourceModel\Listing\Log\CollectionFactory $listingLogCollectionFactory
    ) {
        $this->listingLogCollectionFactory = $listingLogCollectionFactory;
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

    public function getCountErrorsByDateRange(
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $to = null
    ): int {
        $listingLogCollection = $this->listingLogCollectionFactory->create();
        $listingLogCollection->skipIncorrectAccounts();
        $select = $listingLogCollection->getSelect();
        $select->reset('columns');
        $select->columns('COUNT(*)');
        $select->where('main_table.type = ?', \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR);

        if ($from !== null && $to !== null) {
            $select->where(
                sprintf(
                    "main_table.create_date BETWEEN '%s' AND '%s'",
                    $from->format('Y-m-d H:i:s'),
                    $to->format('Y-m-d H:i:s')
                )
            );
        }

        $select->group(['main_table.listing_product_id', 'main_table.description']);

        return $listingLogCollection->getSize();
    }
}
