<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop;

abstract class AbstractMain extends \M2E\TikTokShop\Controller\Adminhtml\AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::tts');
    }

    protected function getCustomViewNick()
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::NICK;
    }

    protected function initResultPage(): void
    {
        if ($this->resultPage !== null) {
            return;
        }
        
        parent::initResultPage();

        $this->getResultPage()
             ->getConfig()
             ->getTitle()->prepend(\M2E\TikTokShop\Helper\View\TikTokShop::getTitle());

        if ($this->getLayoutType() != self::LAYOUT_BLANK) {
            /** @psalm-suppress UndefinedMethod */
            $this->getResultPage()->setActiveMenu(\M2E\TikTokShop\Helper\View\TikTokShop::MENU_ROOT_NODE_NICK);
        }
    }
}
