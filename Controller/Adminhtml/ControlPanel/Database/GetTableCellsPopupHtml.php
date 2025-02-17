<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

class GetTableCellsPopupHtml extends AbstractTable
{
    public function execute()
    {
        $block = $this->getLayout()
                      ->createBlock(
                          \M2E\Core\Block\Adminhtml\ControlPanel\Tab\Database\Table\TableCellsPopup::class
                      );
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
