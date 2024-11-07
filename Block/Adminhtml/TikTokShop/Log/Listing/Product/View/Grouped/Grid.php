<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Log\Listing\Product\View\Grouped;

use M2E\TikTokShop\Block\Adminhtml\Log\Listing\Product\View\Grouped\AbstractGrid;

class Grid extends AbstractGrid
{
    protected function getExcludedActionTitles(): array
    {
        return [
            \M2E\TikTokShop\Model\Listing\Log::ACTION_DELETE_AND_REMOVE_PRODUCT => '',
            \M2E\TikTokShop\Model\Listing\Log::ACTION_DELETE_PRODUCT => '',
            \M2E\TikTokShop\Model\Listing\Log::ACTION_SWITCH_TO_AFN => '',
            \M2E\TikTokShop\Model\Listing\Log::ACTION_SWITCH_TO_MFN => '',
            \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_TIER_PRICE => '',
            \M2E\TikTokShop\Model\Listing\Log::ACTION_RESET_BLOCKED_PRODUCT => '',
        ];
    }
}
