<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop;

abstract class AbstractWizard extends \M2E\TikTokShop\Controller\Adminhtml\AbstractWizard
{
    protected function initResultPage(): void
    {
        parent::initResultPage();
        /** @psalm-suppress UndefinedMethod */
        $this->getResultPage()->setActiveMenu($this->getMenuRootNodeNick());
    }

    protected function getCustomViewNick()
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::NICK;
    }

    protected function getMenuRootNodeNick()
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::MENU_ROOT_NODE_NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::getMenuRootNodeLabel();
    }
}
