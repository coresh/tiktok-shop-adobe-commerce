<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Synchronization;

abstract class AbstractLog extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    protected function initResultPage(): void
    {
        parent::initResultPage();
        /** @psalm-suppress UndefinedMethod */
        $this->getResultPage()->setActiveMenu($this->getMenuRootNodeNick());
    }

    protected function getMenuRootNodeNick(): string
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::MENU_ROOT_NODE_NICK;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::help_center_synchronization_log');
    }
}
