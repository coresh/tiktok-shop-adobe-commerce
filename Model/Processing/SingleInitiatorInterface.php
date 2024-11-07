<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing;

interface SingleInitiatorInterface
{
    public function getInitCommand(): \M2E\TikTokShop\Model\Connector\CommandProcessingInterface;

    public function generateProcessParams(): array;

    /**
     * @return string
     */
    public function getResultHandlerNick(): string;

    public function initLock(LockManager $lockManager): void;
}
