<?php

namespace M2E\TikTokShop\Helper\Module;

class Logger
{
    private \M2E\TikTokShop\Model\Log\System\Repository $logSystemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Log\System\Repository $logSystemRepository
    ) {
        $this->logSystemRepository = $logSystemRepository;
    }

    /**
     * @param mixed $logData
     * @param string $class
     *
     * @return void
     */
    public function process($logData, string $class = 'undefined'): void
    {
        try {
            $info = $this->getLogMessage($logData, $class);
            $info .= $this->getStackTraceInfo();

            $this->systemLog($class, null, $info);
        } catch (\Throwable $e) {
        }
    }

    /**
     * @param string $class
     * @param string|null $message
     * @param string $description
     *
     * @return void
     */
    private function systemLog(string $class, ?string $message, string $description): void
    {
        $this->logSystemRepository->create(
            \M2E\TikTokShop\Model\Log\System::TYPE_LOGGER,
            $class,
            (string)$message,
            $description
        );
    }

    /**
     * @param mixed $logData
     * @param string $type
     *
     * @return string
     */
    private function getLogMessage($logData, string $type): string
    {
        if ($logData instanceof \Magento\Framework\Phrase) {
            $logData = (string)$logData;
        }

        if (!is_string($logData)) {
            $logData = print_r($logData, true);
        }

        // @codingStandardsIgnoreLine
        return '[DATE] ' . date('Y-m-d H:i:s', (int)gmdate('U')) . PHP_EOL .
            '[TYPE] ' . $type . PHP_EOL .
            '[MESSAGE] ' . $logData . PHP_EOL .
            str_repeat('#', 80) . PHP_EOL . PHP_EOL;
    }

    /**
     * @return string
     */
    private function getStackTraceInfo(): string
    {
        $exception = new \Exception('');

        return <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$exception->getTraceAsString()}

TRACE;
    }
}
