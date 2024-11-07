<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel;

class Index extends AbstractMain
{
    public function execute()
    {
        $this->init();

        $block = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs::class, '');
        $block->setData('tab', 'summary');
        $this->addContent($block);

        return $this->getResult();
    }
}
