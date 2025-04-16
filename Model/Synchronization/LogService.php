<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Synchronization;

class LogService
{
    private static bool $isRegisterFatalHandler = false;

    private LogFactory $logFactory;
    private Log\Repository $repository;

    private ?int $operationHistoryId = null;
    private int $initiator = \M2E\Core\Helper\Data::INITIATOR_UNKNOWN;
    private int $task = \M2E\TikTokShop\Model\Synchronization\Log::TASK_OTHER;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        LogFactory $logFactory,
        Log\Repository $repository,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper
    ) {
        $this->logFactory = $logFactory;
        $this->repository = $repository;
        $this->exceptionHelper = $exceptionHelper;
    }

    // ----------------------------------------

    public function setInitiator(int $initiator): void
    {
        $this->initiator = $initiator;
    }

    public function setTask(int $task): void
    {
        $this->task = $task;
    }

    public function setOperationHistoryId(int $id): void
    {
        $this->operationHistoryId = $id;
    }

    // ----------------------------------------

    public function add(
        string $description,
        int $type,
        ?string $detailedDescription = null
    ): void {
        $log = $this->logFactory->create();

        $log->create(
            $this->initiator,
            $this->task,
            $this->operationHistoryId,
            $description,
            $type,
            $detailedDescription,
        );

        $this->repository->save($log);
    }

    public function addFromException(\Throwable $exception): void
    {
        $this->add(
            $exception->getMessage(),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR,
            $this->exceptionHelper->getExceptionDetailedInfo($exception),
        );
    }

    // ----------------------------------------

    public function registerFatalErrorHandler(): void
    {
        if (self::$isRegisterFatalHandler) {
            return;
        }

        self::$isRegisterFatalHandler = true;

        $object = $this;
        // @codingStandardsIgnoreLine
        register_shutdown_function(
            function () use ($object) {
                $error = error_get_last();
                if ($error === null) {
                    return;
                }

                if (!in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
                    return;
                }

                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $traceInfo = $this->exceptionHelper->getFatalStackTraceInfo($trace);

                $object->add(
                    $error['message'],
                    Log::TYPE_FATAL_ERROR,
                    $this->exceptionHelper->getFatalErrorDetailedInfo($error, $traceInfo),
                );
            },
        );
    }
}
