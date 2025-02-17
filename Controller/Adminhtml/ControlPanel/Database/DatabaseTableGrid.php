<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

class DatabaseTableGrid extends AbstractTable
{
    public function execute()
    {
        /** @var \M2E\Core\Block\Adminhtml\ControlPanel\Tab\Database\Table\Grid $grid */
        $grid = $this->getLayout()
                     ->createBlock(
                         \M2E\Core\Block\Adminhtml\ControlPanel\Tab\Database\Table\Grid::class,
                         '',
                         ['tableName' => $this->getRequest()->getParam('table')],
                     );
        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
