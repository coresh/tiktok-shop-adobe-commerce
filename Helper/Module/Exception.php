<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Module;

class Exception
{
    private bool $isRegisterFatalHandler = false;

    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \M2E\TikTokShop\Helper\Module\Log $logHelper;
    private \M2E\TikTokShop\Model\Log\System\Repository $logSystemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Log\System\Repository $logSystemRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\TikTokShop\Helper\Module\Log $logHelper
    ) {
        $this->storeManager = $storeManager;
        $this->logHelper = $logHelper;
        $this->logSystemRepository = $logSystemRepository;
    }

    public function process(\Throwable $throwable, array $context = []): void
    {
        $class = get_class($throwable);
        $info = $this->getExceptionDetailedInfo($throwable, $context);

        $type = \M2E\TikTokShop\Model\Log\System::TYPE_EXCEPTION;
        if ($throwable instanceof \M2E\TikTokShop\Model\Exception\Connection) {
            $type = \M2E\TikTokShop\Model\Log\System::TYPE_EXCEPTION_CONNECTOR;
        }

        $this->systemLog(
            $type,
            $class,
            $throwable->getMessage(),
            $info,
        );
    }

    private function processFatal(array $error, string $traceInfo): void
    {
        try {
            $class = 'Fatal Error';

            if (isset($error['message']) && strpos($error['message'], 'Allowed memory size') !== false) {
                $this->systemLog(
                    \M2E\TikTokShop\Model\Log\System::TYPE_FATAL_ERROR,
                    $class,
                    $error['message'],
                    $this->getFatalInfo($error, 'Fatal Error')
                );

                return;
            }

            $info = $this->getFatalErrorDetailedInfo($error, $traceInfo);

            $this->systemLog(
                \M2E\TikTokShop\Model\Log\System::TYPE_FATAL_ERROR,
                $class,
                $error['message'],
                $info,
            );
        } catch (\Throwable $e) {
        }
    }

    public function setFatalErrorHandler(): void
    {
        if ($this->isRegisterFatalHandler) {
            return;
        }

        $this->isRegisterFatalHandler = true;

        $shutdownFunction = function () {
            $error = error_get_last();

            if ($error === null) {
                return;
            }

            $fatalErrors = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR];

            if (in_array($error['type'], $fatalErrors)) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $traceInfo = $this->getFatalStackTraceInfo($trace);
                $this->processFatal($error, $traceInfo);
            }
        };

        register_shutdown_function($shutdownFunction);
    }

    /**
     * @param \Throwable $exception
     *
     * @return string
     */
    public function getUserMessage(\Throwable $exception): string
    {
        return __('Fatal error occurred') . ': "' . $exception->getMessage() . '".';
    }

    // ----------------------------------------

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFatalErrorDetailedInfo(array $error, string $traceInfo): string
    {
        $info = $this->getFatalInfo($error, 'Fatal Error');
        $info .= $traceInfo;
        $info .= $this->getAdditionalActionInfo();
        $info .= $this->logHelper->platformInfo();
        $info .= $this->logHelper->moduleInfo();

        return $info;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getExceptionDetailedInfo(\Throwable $throwable, array $context = []): string
    {
        $info = $this->getExceptionInfo($throwable, get_class($throwable), $context);
        $info .= $this->getExceptionStackTraceInfo($throwable);
        $info .= $this->getAdditionalActionInfo();
        $info .= $this->logHelper->platformInfo();
        $info .= $this->logHelper->moduleInfo();

        return $info;
    }

    // ----------------------------------------

    private function systemLog(int $type, string $class, string $message, string $description): void
    {
        $trace = debug_backtrace();
        $file = $trace[1]['file'] ?? 'not set';
        $line = $trace[1]['line'] ?? 'not set';

        $additionalData = [
            'called-from' => $file . ' : ' . $line,
        ];

        $this->logSystemRepository->create($type, $class, $message, $description, $additionalData);
    }

    private function getExceptionInfo(\Throwable $throwable, string $type, array $context): string
    {
        $additionalData = $throwable instanceof \M2E\TikTokShop\Model\Exception ? $throwable->getAdditionalData() : [];
        $additionalData = array_merge($additionalData, $context);
        $additionalData = print_r($additionalData, true);

        return <<<EXCEPTION
-------------------------------- EXCEPTION INFO ----------------------------------
Type: {$type}
File: {$throwable->getFile()}
Line: {$throwable->getLine()}
Code: {$throwable->getCode()}
Message: {$throwable->getMessage()}
Additional Data: {$additionalData}

EXCEPTION;
    }

    private function getExceptionStackTraceInfo(\Throwable $throwable): string
    {
        return <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$throwable->getTraceAsString()}

TRACE;
    }

    /**
     * @param array $error
     * @param string $type
     *
     * @return string
     */
    private function getFatalInfo(array $error, string $type): string
    {
        return <<<FATAL
-------------------------------- FATAL ERROR INFO --------------------------------
Type: {$type}
File: {$error['file']}
Line: {$error['line']}
Message: {$error['message']}

FATAL;
    }

    /**
     * @param array $stackTrace
     *
     * @return string
     */
    public function getFatalStackTraceInfo(array $stackTrace): string
    {
        $stackTrace = array_reverse($stackTrace);
        $info = '';

        if (count($stackTrace) > 1) {
            foreach ($stackTrace as $key => $trace) {
                $info .= "#{$key} {$trace['file']}({$trace['line']}):";
                $info .= " {$trace['class']}{$trace['type']}{$trace['function']}(";

                if (!empty($trace['args'])) {
                    foreach ($trace['args'] as $argKey => $arg) {
                        $argKey !== 0 && $info .= ',';

                        if (is_object($arg)) {
                            $info .= get_class($arg);
                        } else {
                            $info .= $arg;
                        }
                    }
                }
                $info .= ")\n";
            }
        }

        if ($info === '') {
            $info = 'Unavailable';
        }

        return <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$info}

TRACE;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAdditionalActionInfo(): string
    {
        $currentStoreId = $this->storeManager->getStore()->getId();

        return <<<ACTION
-------------------------------- ADDITIONAL INFO -------------------------------------
Current Store: {$currentStoreId}

ACTION;
    }
}
