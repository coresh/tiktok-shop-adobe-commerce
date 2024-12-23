<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Account\Grid;

use M2E\TikTokShop\Model\ResourceModel\SearchResultTrait;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection implements SearchResultInterface
{
    use SearchResultTrait;

    public function _construct(): void
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \M2E\TikTokShop\Model\ResourceModel\Account::class,
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this
            ->getSelect()
            ->joinLeft(
                ['shop' => $this->getShopTableSubquery()],
                sprintf(
                    'shop.%s = main_table.%s',
                    \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_ACCOUNT_ID,
                    \M2E\TikTokShop\Model\ResourceModel\Account::COLUMN_ID
                ),
                ['shop_region_codes' => $this->getRegionCodesExpression()]
            )
            ->group('main_table.' . \M2E\TikTokShop\Model\ResourceModel\Account::COLUMN_ID);

        return $this;
    }

    private function getShopTableSubquery(): \Zend_Db_Expr
    {
        return new \Zend_Db_Expr(
            sprintf(
                '(SELECT %s, %s from %s)',
                \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_REGION,
                \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_ACCOUNT_ID,
                $this->getTable(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SHOP),
            )
        );
    }

    private function getRegionCodesExpression(): \Zend_Db_Expr
    {
        return new \Zend_Db_Expr(
            sprintf(
                'GROUP_CONCAT(shop.%s SEPARATOR ";")',
                \M2E\TikTokShop\Model\ResourceModel\Shop::COLUMN_REGION
            )
        );
    }
}
