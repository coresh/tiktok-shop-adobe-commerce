<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

class DeleteTableRows extends AbstractTable
{
    public function execute()
    {
        $ids = $this->prepareIds();
        $modelInstance = $this->getTableModel();

        if (empty($ids)) {
            $this->getMessageManager()->addError('Failed to get model or any of Table Rows are not selected.');
            $this->redirectToTablePage($modelInstance->getTableName());
        }

        $modelInstance->deleteEntries($ids);
        $this->afterTableAction($modelInstance->getTableName());
    }
}
