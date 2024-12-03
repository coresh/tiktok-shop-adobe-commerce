<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Mapping;

class MapGrid extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Listing\Mapping\Grid::class,
            '',
            [
                'data' => [
                    'grid_url' => '*/listing_other_mapping/mapGrid',
                    'mapping_handler_js' => 'ListingOtherMappingObj',
                    'mapping_action' => 'map',
                    'product_type' => $this->getRequest()->getParam('type') ?? ''
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
