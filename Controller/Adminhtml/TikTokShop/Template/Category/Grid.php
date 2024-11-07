<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\Category;

class Grid extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\AbstractCategory
{
    public function execute()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Grid $grid */
        $grid = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Grid::class
        );

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
