<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ManufacturerConfiguration;

class BrandNameResolverResult
{
    private const STATUS_SUCCESS = 'success';
    private const STATUS_FAIL = 'fail';

    private string $status;
    private string $brandName;
    private string $failMessage;

    private function __construct(string $status, string $brandName = '', string $failMessage = '')
    {
        $this->status = $status;
        $this->brandName = $brandName;
        $this->failMessage = $failMessage;
    }

    public static function newSuccess(string $brandName): self
    {
        return new self(self::STATUS_SUCCESS, $brandName);
    }

    public static function newFail(string $failMessage): self
    {
        return new self(self::STATUS_FAIL, '', $failMessage);
    }

    public function isFail(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }

    public function getBrandName(): string
    {
        return $this->brandName;
    }

    public function getFailMessage(): string
    {
        return $this->failMessage;
    }
}
