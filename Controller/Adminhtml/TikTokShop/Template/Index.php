<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate;

class Index extends AbstractTemplate
{
    public function execute()
    {
        $content = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template::class);

        $this->getResult()->getConfig()->getTitle()->prepend('Policies');
        $this->addContent($content);

        return $this->getResult();
    }
}
