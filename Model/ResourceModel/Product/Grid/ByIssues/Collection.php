<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Product\Grid\ByIssues;

use M2E\TikTokShop\Model\ResourceModel\Listing as ListingResource;
use M2E\TikTokShop\Model\ResourceModel\Product as ProductResource;
use M2E\TikTokShop\Model\ResourceModel\Tag as TagResource;
use M2E\TikTokShop\Model\ResourceModel\Tag\ListingProduct\Relation as TagProductRelationResource;
use Magento\Framework\Api\Search\SearchResultInterface;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel implements SearchResultInterface
{
    use \M2E\TikTokShop\Model\ResourceModel\SearchResultTrait;

    private array $accountsIds = [];

    private TagResource $tagResource;
    private ProductResource $productResource;
    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;
    private ListingResource $listingResource;

    public function __construct(
        TagResource $tagResource,
        ProductResource $productResource,
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ListingResource $listingResource,
        \M2E\Core\Helper\Magento\Staging $magentoStagingHelper,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $magentoStagingHelper,
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource,
        );
        $this->tagResource = $tagResource;
        $this->productResource = $productResource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->listingResource = $listingResource;
        $this->prepareCollection();
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
            TagProductRelationResource::class,
        );
    }

    private function prepareCollection(): void
    {
        $this->join(
            ['tag' => $this->tagResource->getMainTable()],
            sprintf(
                'main_table.%s = tag.%s',
                TagProductRelationResource::COLUMN_TAG_ID,
                TagResource::COLUMN_ID,
            ),
        );

        $this->join(
            ['lp' => $this->productResource->getMainTable()],
            sprintf(
                'main_table.%s = lp.%s',
                TagProductRelationResource::COLUMN_LISTING_PRODUCT_ID,
                ProductResource::COLUMN_ID,
            ),
        );

        $this->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $this->getSelect()->columns([
            'total_items' => new \Magento\Framework\DB\Sql\Expression('COUNT(*)'),
            'tag_id' => sprintf('tag.%s', TagResource::COLUMN_ID),
            'text' => sprintf('tag.%s', TagResource::COLUMN_TEXT),
            'error_code' => sprintf('tag.%s', TagResource::COLUMN_ERROR_CODE),
        ]);

        $this->getSelect()->where(
            sprintf('tag.%s != ?', TagResource::COLUMN_ERROR_CODE),
            \M2E\TikTokShop\Model\Tag::HAS_ERROR_ERROR_CODE,
        );
        $this->getSelect()->group(
            sprintf('main_table.%s', TagProductRelationResource::COLUMN_TAG_ID),
        );
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'account_id') {
            $this->accountsIds = $condition['in'];

            return $this;
        }

        parent::addFieldToFilter($field, $condition);

        return $this;
    }

    public function getItems()
    {
        // subquery in the getItems because filters initialized only here
        $allItemsSubSelect = $this->getAllItemsSubSelect();

        $this->getSelect()->columns([
            'impact_rate' => new \Magento\Framework\DB\Sql\Expression('COUNT(*) * 100 /(' . $allItemsSubSelect . ')'),
        ]);

        return parent::getItems();
    }

    private function getAllItemsSubSelect(): \Magento\Framework\DB\Select
    {
        $collection = $this->productCollectionFactory->create();

        if (!empty($this->accountsIds)) {
            $collection->joinInner(
                ['l' => $this->listingResource->getMainTable()],
                sprintf(
                    'l.%s=main_table.%s',
                    ListingResource::COLUMN_ID,
                    ProductResource::COLUMN_LISTING_ID,
                ),
                [],
            );
            $collection->getSelect()->where(
                sprintf('l.%s IN (?)', ListingResource::COLUMN_ACCOUNT_ID),
                $this->accountsIds,
            );
        }

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns('COUNT(*)');

        return $collection->getSelect();
    }
}
