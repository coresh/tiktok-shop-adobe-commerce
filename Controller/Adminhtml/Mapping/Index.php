<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Mapping;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate;

class Index extends AbstractTemplate
{
    protected function getLayoutType()
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\Mapping\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Mapping\Tabs::class,
        );

        if ($this->isAjax()) {
            $this->setAjaxContent(
                $tabsBlock->getTabContent($tabsBlock->getActiveTab())
            );

            return $this->getResult();
        }

        $this->addLeft($tabsBlock);
        $this->addContent($this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Mapping::class));

        $this->getResult()->getConfig()->getTitle()->prepend(__('Mapping'));

        return $this->getResult();
    }
}
