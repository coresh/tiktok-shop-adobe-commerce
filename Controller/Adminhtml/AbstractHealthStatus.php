<?php

namespace M2E\TikTokShop\Controller\Adminhtml;

abstract class AbstractHealthStatus extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    protected function getLayoutType(): string
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::help_center_health_status');
    }

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
}
