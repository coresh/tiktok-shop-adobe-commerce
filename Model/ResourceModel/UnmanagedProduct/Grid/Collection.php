<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\Grid;

use M2E\TikTokShop\Model\ResourceModel\Product as ProductResource;
use M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku as VariantSkuResource;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection implements
    \Magento\Framework\Api\Search\SearchResultInterface
{
    use \M2E\TikTokShop\Model\ResourceModel\SearchResultTrait;

    protected $_idFieldName = 'id';
    private ProductResource $productResource;
    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku $variantSkuResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct\VariantSku $variantSkuResource,
        ProductResource $productResource,
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource,
        );
        $this->variantSkuResource = $variantSkuResource;
        $this->productResource = $productResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->prepareCollection();
    }

    protected function _initSelect()
    {
        $this->addFilterToMap('id', 'main_table.id');

        parent::_initSelect();
    }

    public function _construct(): void
    {
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::class,
        );
    }

    private function prepareCollection(): void
    {
        $this->getSelect()->joinLeft(
            ['variant_sku' => $this->variantSkuResource->getMainTable()],
            sprintf(
                'main_table.id = variant_sku.product_id AND variant_sku.id = (
                SELECT MIN(vs.id)
                FROM %s vs
                WHERE vs.product_id = main_table.id
            )',
                $this->variantSkuResource->getMainTable()
            ),
            [
                'sku_id' => 'sku_id',
                'price' => 'variant_sku.price',
            ]
        );
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'account') {
            $field = 'main_table.account_id';
        }

        if ($field === 'sku_id') {
            $this->buildSkuIdFilter($condition);
            return $this;
        }

        if ($field === 'qty') {
            $this->buildQtyFilter($condition);
            return $this;
        }

        if ($field === 'price') {
            $this->buildPriceFilter($condition);
            return $this;
        }

        if ($field === 'shop_id') {
            $this->buildShopFilter($condition);
            return $this;
        }

        if ($field === 'linked') {
            $this->buildFilterByLinked($condition);

            return $this;
        }

        parent::addFieldToFilter($field, $condition);

        return $this;
    }

    private function buildSkuIdFilter($condition): void
    {
        $this->getSelect()->where(
            sprintf(
                '(main_table.%s = 1 AND variant_sku.sku_id LIKE ?)',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_IS_SIMPLE
            ),
            $condition['like']
        );
    }

    private function buildQtyFilter($condition): void
    {
        $where = '';

        if (isset($condition['gteq']) && $condition['gteq'] !== '') {
            $where .= sprintf(
                'main_table.%s >= %s',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_QTY,
                $this->getConnection()->quote($condition['gteq'])
            );
        }

        if (isset($condition['lteq']) && $condition['lteq'] !== '') {
            if ($where !== '') {
                $where .= ' AND ';
            }

            $where .= sprintf(
                'main_table.%s <= %s',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_QTY,
                $this->getConnection()->quote($condition['lteq'])
            );
        }

        if ($where) {
            $this->getSelect()->where($where);
        }
    }

    private function buildPriceFilter($condition): void
    {
        $where = '';

        if (isset($condition['gteq']) && $condition['gteq'] !== '') {
            $where .= sprintf(
                '(%s >= %s)',
                'price',
                $this->getConnection()->quote($condition['gteq'])
            );

            $where .= sprintf(
                ' AND (%s >= %s)',
                'variant_sku.price',
                $this->getConnection()->quote($condition['gteq'])
            );
        }

        if (isset($condition['lteq']) && $condition['lteq'] !== '') {
            if ($where !== '') {
                $where .= ' AND ';
            }

            $where .= sprintf(
                '(%s <= %s)',
                'price',
                $this->getConnection()->quote($condition['lteq'])
            );

            $where .= sprintf(
                ' AND (%s <= %s)',
                'variant_sku.price',
                $this->getConnection()->quote($condition['lteq'])
            );
        }

        if ($where) {
            $this->getSelect()->where($where);
        }
    }

    private function buildShopFilter($condition): void
    {
        $this->getSelect()->where(
            sprintf(
                'main_table.%s IN (%s)',
                \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_SHOP_ID,
                implode(',', array_map([$this->getConnection(), 'quote'], $condition['in']))
            )
        );
    }

    private function buildFilterByLinked($condition): void
    {
        $conditionValue = (int)$condition['eq'];
        $column = \M2E\TikTokShop\Model\ResourceModel\UnmanagedProduct::COLUMN_MAGENTO_PRODUCT_ID;

        if ($conditionValue === \M2E\TikTokShop\Ui\Select\YesNoAnyOption::OPTION_YES) {
            $this->getSelect()->where(sprintf('main_table.%s IS NOT NULL', $column));
        } elseif ($conditionValue === \M2E\TikTokShop\Ui\Select\YesNoAnyOption::OPTION_NO) {
            $this->getSelect()->where(sprintf('main_table.%s IS NULL', $column));
        }
    }
}
