<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Account;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount;

class NewAction extends AbstractAccount
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
