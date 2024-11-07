<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

use M2E\TikTokShop\Controller\Adminhtml\AbstractOrder;

class ProductMappingGrid extends AbstractOrder
{
    public function execute()
    {
        $grid = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Item\Product\Mapping\Grid::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
