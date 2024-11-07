<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractTemplate;

class TemplateGrid extends AbstractTemplate
{
    public function execute()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Grid $switcherBlock */
        $grid = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
