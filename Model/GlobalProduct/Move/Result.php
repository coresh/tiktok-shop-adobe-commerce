<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\GlobalProduct\Move;

class Result
{
    private const STATUS_FAIL = 'fail';
    private const STATUS_SUCCESS = 'success';

    private string $status;
    private array $failMessages;

    private function __construct(string $status, array $failMessages = [])
    {
        $this->status = $status;
        $this->failMessages = $failMessages;
    }

    public static function createFail(array $failMessages): Result
    {
        return new self(self::STATUS_FAIL, $failMessages);
    }

    public static function createSuccess(): Result
    {
        return new self(self::STATUS_SUCCESS);
    }

    // ----------------------------------------

    public function getFailMessages(): array
    {
        return $this->failMessages;
    }

    public function isFail(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }
}
