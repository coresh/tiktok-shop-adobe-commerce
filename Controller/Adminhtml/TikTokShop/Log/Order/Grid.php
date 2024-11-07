<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Log\Order;

class Grid extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Log\AbstractOrder
{
    public function execute()
    {
        $response = $this->getLayout()
                         ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Log\Order\Grid::class)
                         ->toHtml();
        $this->setAjaxContent($response);

        return $this->getResult();
    }
}
