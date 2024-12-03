<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\Unmanaged;

class ExportCsvUnmanagedGrid extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractMain
{
    private \M2E\TikTokShop\Helper\Data\FileExport $fileExportHelper;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\FileExport $fileExportHelper,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Account\Ui\RuntimeStorage $uiAccountRuntimeStorage
    ) {
        parent::__construct();

        $this->fileExportHelper = $fileExportHelper;
        $this->accountRepository = $accountRepository;
        $this->uiAccountRuntimeStorage = $uiAccountRuntimeStorage;
    }

    public function execute()
    {
        $this->loadAccount();
        $gridName = \M2E\TikTokShop\Helper\Data\FileExport::UNMANAGED_GRID;

        $content = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged\Grid::class)
            ->getCsv();

        return $this->fileExportHelper->createFile($gridName, $content);
    }

    private function loadAccount(): void
    {
        $accountId = $this->getRequest()->getParam('account');
        if (empty($accountId)) {
            $account = $this->accountRepository->getFirst();
        } else {
            $account = $this->accountRepository->get((int)$accountId);
        }

        $this->uiAccountRuntimeStorage->setAccount($account);
    }
}
