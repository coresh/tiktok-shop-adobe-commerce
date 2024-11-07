<?php

namespace M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer\ViewLogIcon;

use M2E\TikTokShop\Block\Adminhtml\Traits;
use M2E\TikTokShop\Model\Listing\Log;
use M2E\TikTokShop\Model\ResourceModel\Listing\Log as ListingLogResource;

class Listing extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    use Traits\BlockTrait;

    private ListingLogResource $listingLogResource;

    public function __construct(
        ListingLogResource $listingLogResource,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->listingLogResource = $listingLogResource;
    }

    protected function getAvailableActions(): array
    {
        return [
            Log::ACTION_LIST_PRODUCT => (string)__('List'),
            Log::ACTION_RELIST_PRODUCT => (string)__('Relist'),
            Log::ACTION_REVISE_PRODUCT => (string)__('Revise'),
            Log::ACTION_STOP_PRODUCT => (string)__('Stop'),
            Log::ACTION_REMAP_LISTING_PRODUCT => (string)__('Relink'),
            Log::ACTION_REMOVE_PRODUCT => (string)__('Remove from Channel / Remove from Listing'),
            Log::ACTION_CHANNEL_CHANGE => (string)__('External Change'),
        ];
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $listingProductId = (int)$row->getData('id');
        $availableActionsId = array_keys($this->getAvailableActions());

        $connection = $this->listingLogResource->getConnection();

        // Get last messages
        // ---------------------------------------
        $dbSelect = $connection->select()
                               ->from(
                                   $this->listingLogResource->getMainTable(),
                                   [
                                       ListingLogResource::COLUMN_ACTION_ID,
                                       ListingLogResource::COLUMN_ACTION,
                                       ListingLogResource::COLUMN_TYPE,
                                       ListingLogResource::COLUMN_DESCRIPTION,
                                       ListingLogResource::COLUMN_CREATE_DATE,
                                       ListingLogResource::COLUMN_INITIATOR,
                                       ListingLogResource::COLUMN_LISTING_PRODUCT_ID,
                                   ]
                               )
                               ->where(sprintf('`%s` IS NOT NULL', ListingLogResource::COLUMN_ACTION))
                               ->where(sprintf('`%s` IN (?)', ListingLogResource::COLUMN_ACTION), $availableActionsId)
                               ->order(['id DESC'])
                               ->limit(\M2E\TikTokShop\Block\Adminhtml\Log\Grid\LastActions::PRODUCTS_LIMIT);

        $dbSelect->where(sprintf('`%s` = ?', ListingLogResource::COLUMN_LISTING_PRODUCT_ID), $listingProductId);

        $logs = $connection->fetchAll($dbSelect);

        if (empty($logs)) {
            return '';
        }

        return $this->getLastActions($listingProductId, $logs);
    }

    protected function getLastActions($listingProductId, $logs)
    {
        $summary = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Listing\Log\Grid\LastActions::class)
                        ->setData([
                            'entity_id' => $listingProductId,
                            'logs' => $logs,
                            'available_actions' => $this->getAvailableActions(),
                            'view_help_handler' => "{$this->getJsHandler()}.viewItemHelp",
                            'hide_help_handler' => "{$this->getJsHandler()}.hideItemHelp",
                        ]);

        return $summary->toHtml();
    }

    protected function getJsHandler()
    {
        if ($this->hasData('jsHandler')) {
            return $this->getData('jsHandler');
        }

        return 'ListingGridObj';
    }
}
