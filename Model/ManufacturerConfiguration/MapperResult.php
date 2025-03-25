<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ManufacturerConfiguration;

class MapperResult
{
    private const STATUS_SUCCESS = 'success';
    private const STATUS_FAIL = 'fail';

    private string $status;
    private string $failMessage;

    private function __construct(string $status, string $failMessage = '')
    {
        $this->status = $status;
        $this->failMessage = $failMessage;
    }

    public static function newSuccess(): self
    {
        return new self(self::STATUS_SUCCESS);
    }

    public static function newFail(string $failMessage): self
    {
        return new self(self::STATUS_FAIL, $failMessage);
    }

    public function isFail(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }

    public function getFailMessage(): string
    {
        return $this->failMessage;
    }
}
