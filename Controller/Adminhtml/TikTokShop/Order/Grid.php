<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Order;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder;

class Grid extends AbstractOrder
{
    public function execute()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\Grid $grid */
        $grid = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
