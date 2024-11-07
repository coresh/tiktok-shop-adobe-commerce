<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Account;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount;

class Index extends AbstractAccount
{
    public function execute()
    {
        $this->getResultPage()
             ->getConfig()
             ->getTitle()
             ->prepend(__('Accounts'));

        return $this->getResultPage();
    }
}
