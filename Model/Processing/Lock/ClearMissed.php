<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\Lock;

class ClearMissed
{
    /** @var \M2E\TikTokShop\Model\Processing\Lock\Repository */
    private Repository $repository;
    private \M2E\TikTokShop\Helper\Module\Logger $logger;

    public function __construct(
        Repository $repository,
        \M2E\TikTokShop\Helper\Module\Logger $logger
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function process(): void
    {
        $lockData = [];
        foreach ($this->repository->findMissedLocks() as $lock) {
            $lockData[$lock->getNick()][$lock->getObjectId()] = $lock->getTag();

            $this->repository->remove($lock);
        }

        if (!empty($lockData)) {
            $this->logger->process($lockData, 'Processing Locks Records were broken and removed');
        }
    }
}
