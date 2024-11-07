<?php

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

/**
 * Class \M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database\DeleteTableRows
 */
class DeleteTableRows extends AbstractTable
{
    public function execute()
    {
        $ids = $this->prepareIds();
        $modelInstance = $this->getTableModel();

        if (empty($ids)) {
            $this->getMessageManager()->addError("Failed to get model or any of Table Rows are not selected.");
            $this->redirectToTablePage($modelInstance->getTableName());
        }

        $modelInstance->deleteEntries($ids);
        $this->afterTableAction($modelInstance->getTableName());
    }
}
