<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\ProcessResult;

class Partial
{
    private const MAX_RECORDS_ON_RUN = 5;

    private \M2E\TikTokShop\Model\Processing\Repository $repository;
    private \M2E\TikTokShop\Model\Processing\ResultHandlerFactory $resultHandlerFactory;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\Processing\LockManagerFactory $lockManagerFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Processing\Repository $repository,
        \M2E\TikTokShop\Model\Processing\ResultHandlerFactory $resultHandlerFactory,
        \M2E\TikTokShop\Model\Processing\LockManagerFactory $lockManagerFactory,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper
    ) {
        $this->repository = $repository;
        $this->resultHandlerFactory = $resultHandlerFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->lockManagerFactory = $lockManagerFactory;
    }

    public function processExpired(): void
    {
        foreach ($this->repository->findPartialTypeExpired() as $processing) {
            try {
                $lockManager = $this->lockManagerFactory->create($processing);

                /** @var \M2E\TikTokShop\Model\Processing\PartialResultHandlerInterface $handler */
                $handler = $this->resultHandlerFactory->create($processing->getHandleNick());

                $handler->initialize($processing->getParams());

                $handler->processExpire();

                $handler->clearLock($lockManager);

                $this->repository->remove($processing);
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e);

                $this->repository->forceRemove($processing);
            }
        }
    }

    public function processData(): void
    {
        foreach ($this->repository->findPartialTypeForProcess(self::MAX_RECORDS_ON_RUN) as $processing) {
            try {
                $lockManager = $this->lockManagerFactory->create($processing);

                /** @var \M2E\TikTokShop\Model\Processing\PartialResultHandlerInterface $handler */
                $handler = $this->resultHandlerFactory->create($processing->getHandleNick());

                $handler->initialize($processing->getParams());

                foreach ($processing->getPartialData() as $partialData) {
                    $handler->processPartialResult($partialData->getResultData());
                }

                $handler->processSuccess([], $processing->getResultMessages());

                $handler->clearLock($lockManager);

                $this->repository->remove($processing);
            } catch (\Throwable $e) {
                $this->exceptionHelper->process($e);

                $this->repository->forceRemove($processing);
            }
        }
    }
}
