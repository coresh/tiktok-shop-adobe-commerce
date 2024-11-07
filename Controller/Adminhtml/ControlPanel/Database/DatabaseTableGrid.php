<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

class DatabaseTableGrid extends AbstractTable
{
    public function execute()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Database\Table\Grid $grid */
        $grid = $this->getLayout()
                     ->createBlock(\M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Database\Table\Grid::class);
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
