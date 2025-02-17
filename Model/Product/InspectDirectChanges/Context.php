<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\InspectDirectChanges;

class Context
{
    private const REGISTRY_KEY = '/listing/product/inspector/last_product_id/';

    private \M2E\TikTokShop\Model\Registry\Manager $register;

    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $register
    ) {
        $this->register = $register;
    }

    public function getLastProductId(): int
    {
        $configValue = $this->register->getValue(
            self::REGISTRY_KEY
        );

        if ($configValue === null) {
            return 0;
        }

        return (int)$configValue;
    }

    public function setLastProductId(int $id): void
    {
        $this->register->setValue(
            self::REGISTRY_KEY,
            (string)$id
        );
    }
}
