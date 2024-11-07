<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

class AddTableRow extends AbstractTable
{
    public function execute(): void
    {
        $modelInstance = $this->getTableModel();
        $cellsValues = $this->prepareCellsValuesArray();

        if (empty($cellsValues)) {
            return;
        }

        $modelInstance->createEntry($cellsValues);
        $this->afterTableAction($modelInstance->getTableName());
    }
}
