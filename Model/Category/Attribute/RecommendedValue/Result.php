<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Attribute\RecommendedValue;

class Result
{
    private const STATUS_FAIL = 'fail';
    private const STATUS_SUCCESS = 'success';

    private string $status;
    private string $errorMessage;
    private string $id;

    private function __construct(
        string $status,
        string $errorMessage,
        string $id
    ) {
        $this->status = $status;
        $this->errorMessage = $errorMessage;
        $this->id = $id;
    }

    public static function createFail(string $errorMessage): Result
    {
        return new self(self::STATUS_FAIL, $errorMessage, '');
    }

    public static function createSuccess(string $id): Result
    {
        return new self(self::STATUS_SUCCESS, '', $id);
    }

    // ----------------------------------------

    public function getFailMessages(): string
    {
        return $this->errorMessage;
    }

    public function isFail(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }

    public function getResult(): array
    {
        return ['id' => $this->id];
    }
}
