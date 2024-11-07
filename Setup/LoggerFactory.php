<?php

namespace M2E\TikTokShop\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;

class LoggerFactory
{
    private const LOGFILE_NAME = 'setup-error.log';

    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private DirectoryList $directoryList;
    private \Magento\Framework\Filesystem $fileSystem;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        $this->objectManager = $objectManager;
        $this->directoryList = $directoryList;
        $this->fileSystem = $fileSystem;
    }

    public function create(): \Psr\Log\LoggerInterface
    {
        $streamHandler = new \Monolog\Handler\StreamHandler($this->getLogFilePath());
        $streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter());

        /** @var \Psr\Log\LoggerInterface */
        return $this->objectManager->create(
            \Magento\Framework\Logger\Monolog::class,
            [
                'name' => 'TikTokShop-setup-log',
                'handlers' => [$streamHandler],
            ],
        );
    }

    public function getLogFilePath(): string
    {
        return $this->directoryList->getPath(DirectoryList::LOG) . DIRECTORY_SEPARATOR . $this->generateLogFilePath();
    }

    public function isExistLog(): bool
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::LOG)
                                ->isExist($this->generateLogFilePath());
    }

    public function removeLog(): void
    {
        $this->fileSystem->getDirectoryWrite(DirectoryList::LOG)
                         ->delete($this->generateLogFilePath());
    }

    private function generateLogFilePath(): string
    {
        return 'm2e_tiktokshop' . DIRECTORY_SEPARATOR . self::LOGFILE_NAME;
    }
}
