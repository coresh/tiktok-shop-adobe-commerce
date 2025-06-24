<?php

namespace M2E\TikTokShop\Model\ResourceModel\ScheduledAction;

use M2E\TikTokShop\Model\ResourceModel\ScheduledAction as ScheduledActionResource;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    /** how much time should pass to increase priority value by 1 */
    private const SECONDS_TO_INCREMENT_PRIORITY = 30;

    private \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource;
    private \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource,
        \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource,
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
            $resource
        );
        $this->listingResource = $listingResource;
        $this->listingProductResource = $listingProductResource;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\ScheduledAction::class,
            \M2E\TikTokShop\Model\ResourceModel\ScheduledAction::class
        );
    }

    // ----------------------------------------

    /**
     * @param int $priority
     * @param int $actionType
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getScheduledActionsPreparedCollection(int $priority, int $actionType): self
    {
        $this->getSelect()->joinLeft(
            ['lp' => $this->listingProductResource->getMainTable()],
            'main_table.listing_product_id = lp.id'
        );
        $this->getSelect()->joinLeft(
            ['l' => $this->listingResource->getMainTable()],
            'lp.listing_id = l.id'
        );

        $this->addFieldToFilter('main_table.action_type', $actionType);

        $now = \M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
        $this->getSelect()
             ->reset(\Magento\Framework\DB\Select::COLUMNS)
             ->columns(
                 [
                     'id' => sprintf('main_table.%s', ScheduledActionResource::COLUMN_ID),
                     'listing_product_id' => sprintf(
                         'main_table.%s',
                         ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID
                     ),
                     'account_id' => 'l.account_id',
                     'action_type' => sprintf('main_table.%s', ScheduledActionResource::COLUMN_ACTION_TYPE),
                     'tag' => new \Zend_Db_Expr('NULL'),
                     'additional_data' => sprintf('main_table.%s', ScheduledActionResource::COLUMN_ADDITIONAL_DATA),
                     'coefficient' => new \Zend_Db_Expr(
                         "$priority +
                        (time_to_sec(timediff('$now', main_table.create_date)) / "
                         . self::SECONDS_TO_INCREMENT_PRIORITY . ")"
                     ),
                     'create_date' => 'create_date',
                 ]
             );

        return $this;
    }

    /**
     * @param string $tag
     * @param bool $canBeEmpty
     *
     * @return $this
     */
    public function addTagFilter(string $tag, bool $canBeEmpty = false): self
    {
        $whereExpression = sprintf("main_table.%s LIKE '%%$tag%%'", ScheduledActionResource::COLUMN_TAG);
        if ($canBeEmpty) {
            $whereExpression .= sprintf(
                " OR main_table.%s IS NULL OR main_table.%s = ''",
                ScheduledActionResource::COLUMN_TAG,
                ScheduledActionResource::COLUMN_TAG,
            );
        }

        $this->getSelect()->where($whereExpression);

        return $this;
    }
}
