<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\Unmanaged;

class ExportCsvUnmanagedGrid extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractMain
{
    private \M2E\TikTokShop\Helper\Data\FileExport $fileExportHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\FileExport $fileExportHelper
    ) {
        parent::__construct();

        $this->fileExportHelper = $fileExportHelper;
    }

    public function execute()
    {
        $gridName = \M2E\TikTokShop\Helper\Data\FileExport::UNMANAGED_GRID;

        $content = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged\Grid::class)
            ->getCsv();

        return $this->fileExportHelper->createFile($gridName, $content);
    }
}
