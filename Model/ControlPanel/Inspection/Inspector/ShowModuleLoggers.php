<?php

namespace M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector;

class ShowModuleLoggers implements \M2E\Core\Model\ControlPanel\Inspection\InspectorInterface
{
    private array $loggers = [];

    private \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory;
    private \M2E\Core\Helper\Client $clientHelper;

    public function __construct(
        \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory,
        \M2E\Core\Helper\Client $clientHelper
    ) {
        $this->issueFactory = $issueFactory;
        $this->clientHelper = $clientHelper;
    }

    public function process(): array
    {
        $issues = [];
        $this->searchLoggers();

        if (!empty($this->loggers)) {
            $issues[] = $this->issueFactory->create(
                'TikTokShop loggers were found in magento files',
                $this->loggers,
            );
        }

        return $issues;
    }

    private function searchLoggers(): void
    {
        $recursiveIteratorIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->clientHelper->getBaseDirectory() . 'vendor',
                \FilesystemIterator::FOLLOW_SYMLINKS,
            ),
        );

        foreach ($recursiveIteratorIterator as $splFileInfo) {
            /**@var \SplFileInfo $splFileInfo */

            if (
                !$splFileInfo->isFile()
                || !in_array($splFileInfo->getExtension(), ['php', 'phtml'])
            ) {
                continue;
            }

            if (strpos($splFileInfo->getRealPath(), 'M2E' . DIRECTORY_SEPARATOR . 'TikTokShop') !== false) {
                continue;
            }

            $splFileObject = $splFileInfo->openFile();
            if (!$splFileObject->getSize()) {
                continue;
            }

            $content = $splFileObject->fread($splFileObject->getSize());
            if (strpos($content, 'Module\Logger') === false) {
                continue;
            }

            $content = explode("\n", $content);
            foreach ($content as $line => $contentRow) {
                if (strpos($contentRow, 'Module\Logger') === false) {
                    continue;
                }

                $this->loggers[] = $splFileObject->getRealPath() . ' in line ' . $line;
            }
        }
    }
}
