<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Send\Entity;

class Response
{
    private bool $isSuccess;
    private ?string $packageId;
    private array $errorMessages;
    private array $warningMessages;

    public function __construct(
        bool $isSuccess,
        ?string $packageId,
        array $errorMessages = [],
        array $warningMessages = []
    ) {
        $this->isSuccess = $isSuccess;
        $this->packageId = $packageId;
        $this->errorMessages = $errorMessages;
        $this->warningMessages = $warningMessages;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getPackageId(): ?string
    {
        return $this->packageId;
    }

    public function hasWarnings(): bool
    {
        return count($this->warningMessages) > 0;
    }

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * @return \M2E\TikTokShop\Model\Response\Message[]
     */
    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }
}
