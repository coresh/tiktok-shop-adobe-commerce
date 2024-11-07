<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

/**
 * Class \M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database\GetTableCellsPopupHtml
 */
class GetTableCellsPopupHtml extends AbstractTable
{
    public function execute()
    {
        $block = $this->getLayout()
                      ->createBlock(
                          \M2E\TikTokShop\Block\Adminhtml\ControlPanel\Tabs\Database\Table\TableCellsPopup::class
                      );
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
