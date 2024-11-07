<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Mapping;

class MapProductPopupHtml extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Listing\Mapping\View::class,
            '',
            [
                'data' => [
                    'grid_url' => '*/listing_other_mapping/mapGrid',
                    'mapping_handler_js' => 'ListingOtherMappingObj',
                    'mapping_action' => 'map',
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
