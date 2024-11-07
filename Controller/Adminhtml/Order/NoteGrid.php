<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

use M2E\TikTokShop\Controller\Adminhtml\AbstractOrder;

class NoteGrid extends AbstractOrder
{
    public function execute()
    {
        $grid = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Note\Grid::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
