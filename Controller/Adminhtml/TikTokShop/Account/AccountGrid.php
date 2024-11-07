<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Account;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount;

class AccountGrid extends AbstractAccount
{
    public function execute()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Grid $switcherBlock */
        $grid = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
