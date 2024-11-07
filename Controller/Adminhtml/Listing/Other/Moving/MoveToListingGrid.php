<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Moving;

class MoveToListingGrid extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalData;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\GlobalData $globalData,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->globalData = $globalData;
    }

    public function execute()
    {
        $this->globalData->setValue(
            'accountId',
            $this->getRequest()->getParam('accountId')
        );

        $this->globalData->setValue(
            'shopId',
            $this->getRequest()->getParam('shopId')
        );

        $this->globalData->setValue(
            'ignoreListings',
            \M2E\TikTokShop\Helper\Json::decode($this->getRequest()->getParam('ignoreListings'))
        );

        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Listing\Moving\Grid::class,
            '',
            [
                'data' => [
                    'grid_url' => $this->getUrl(
                        '*/listing_other_moving/moveToListingGrid',
                        ['_current' => true]
                    ),
                    'moving_handler_js' => 'TiktokshopListingOtherGridObj.movingHandler',
                ],
            ]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
