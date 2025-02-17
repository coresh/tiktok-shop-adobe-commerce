<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Unmanaged\Moving;

class MoveToListingGrid extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Listing\Moving\Grid::class,
            '',
            [
                'accountId' => (int)$this->getRequest()->getParam('account_id'),
                'shopId' => (int)$this->getRequest()->getParam('shop_id'),
                'data' => [
                    'grid_url' => $this->getUrl(
                        '*/product_unmanaged_moving/MoveToListingGrid',
                        ['_current' => true]
                    ),
                ],
            ]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
