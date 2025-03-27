<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Ui\Export;

class GridToCsv extends \M2E\TikTokShop\Controller\Adminhtml\AbstractMain
{
    private \M2E\Core\Model\Ui\Export\ConvertToCsv $converter;
    private \Magento\Framework\App\Response\Http\FileFactory $fileFactory;

    public function __construct(
        \M2E\Core\Model\Ui\Export\ConvertToCsv $converter,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct();

        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
    }

    public function execute()
    {
        return $this->fileFactory->create(
            $this->generateExportFileName(),
            $this->converter->getCsvFile(),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::listings');
    }

    private function generateExportFileName(): string
    {
        $date = \M2E\Core\Helper\Date::createCurrentGmt();
        $dateString = $date->format('Ymd_His');

        return 'All_' . $dateString . '.csv';
    }
}
