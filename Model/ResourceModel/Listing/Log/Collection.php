<?php

namespace M2E\TikTokShop\Model\ResourceModel\Listing\Log;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    /** @var \M2E\TikTokShop\Model\ResourceModel\Account */
    private $accountResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Account $accountResource,
        \M2E\TikTokShop\Helper\Magento\Staging $magentoStagingHelper,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
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
        $this->accountResource = $accountResource;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Listing\Log::class,
            \M2E\TikTokShop\Model\ResourceModel\Listing\Log::class
        );
    }

    /**
     * GroupBy fix
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $originSelect = clone $this->getSelect();
        $originSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $originSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $originSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $originSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $originSelect->columns(['*']);

        $countSelect = clone $originSelect;
        $countSelect->reset();
        $countSelect->from($originSelect, null);
        $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));

        return $countSelect;
    }

    public function skipIncorrectAccounts(): void
    {
        $this->getSelect()->joinInner(
            ['account' => $this->accountResource->getMainTable()],
            'main_table.account_id = account.id',
            []
        );
    }
}
