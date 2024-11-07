<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Support;

class Index extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::help_center_m2e_support');
    }

    public function execute()
    {
        $this->addContent(
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Support::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend((string)__('Contact Us'));

        return $this->getResult();
    }

    protected function initResultPage(): void
    {
        parent::initResultPage();
        /** @psalm-suppress UndefinedMethod */
        $this->getResultPage()->setActiveMenu(\M2E\TikTokShop\Helper\View\TikTokShop::MENU_ROOT_NODE_NICK);
    }
}
