<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Unmanaged\Mapping;

class MapGrid extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Listing\Mapping\Grid::class,
            '',
            [
                'data' => [
                    'account_id' => $accountId,
                    'other_product_id' => (int)$this->getRequest()->getParam('other_product_id'),
                    'grid_url' => '*/product_unmanaged_mapping/mapGrid',
                    'product_type' => $this->getRequest()->getParam('type') ?? ''
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
