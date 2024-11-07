<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct;

class Relation extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_PRODUCT_ID = 'listing_product_id';
    public const COLUMN_TAG_ID = 'tag_id';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct(): void
    {
        $this->_init(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_TAG_RELATION,
            self::COLUMN_ID
        );
    }

    /**
     * @param list<list<int>> $dataPackage
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertTags(array $dataPackage): void
    {
        $queryData = [];
        $createDate = \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
        foreach ($dataPackage as $listingProductId => $tagIds) {
            foreach ($tagIds as $tagId) {
                $queryData[] = [
                    self::COLUMN_LISTING_PRODUCT_ID => $listingProductId,
                    self::COLUMN_TAG_ID => $tagId,
                    self::COLUMN_CREATE_DATE => $createDate,
                ];
            }
        }

        if (!empty($queryData)) {
            $this->getConnection()->insertMultiple(
                $this->getMainTable(),
                $queryData
            );
        }
    }

    /**
     * @param list<list<int>> $dataPackage
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeTags(array $dataPackage): void
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable());

        $conditionExists = false;
        foreach ($dataPackage as $listingProductId => $tagIds) {
            foreach ($tagIds as $tagId) {
                $conditionExists = true;
                $select->orWhere(
                    self::COLUMN_LISTING_PRODUCT_ID . " = {$listingProductId}"
                    . ' AND '
                    . self::COLUMN_TAG_ID . " = {$tagId}"
                );
            }
        }

        if ($conditionExists) {
            $connection->query(
                $select->deleteFromSelect($this->getMainTable())
            );
        }
    }
}
