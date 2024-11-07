<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\ControlPanel\Database;

class ShowOperationHistoryExecutionTreeUp extends AbstractTable
{
    private \M2E\TikTokShop\Model\OperationHistory\Repository $repository;

    public function __construct(
        \M2E\TikTokShop\Model\OperationHistory\Repository $repository,
        \M2E\TikTokShop\Helper\Module $moduleHelper,
        \M2E\TikTokShop\Model\ControlPanel\Database\TableModelFactory $databaseTableFactory,
        \M2E\TikTokShop\Model\Module $module
    ) {
        parent::__construct($moduleHelper, $databaseTableFactory, $module);
        $this->repository = $repository;
    }

    public function execute()
    {
        $operationHistoryId = $this->getRequest()->getParam('operation_history_id');
        if (empty($operationHistoryId)) {
            $this->getMessageManager()->addErrorMessage('Operation history ID is not presented.');

            $this->redirectToTablePage(
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY,
            );

            //exit
        }

        $operationHistory = $this->repository->get((int)$operationHistoryId);

        $this->getResponse()->setBody(
            '<pre>' . $operationHistory->getExecutionTreeUpInfo() . '</pre>',
        );
    }
}
