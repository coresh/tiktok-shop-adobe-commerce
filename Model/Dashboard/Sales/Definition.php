<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Sales;

class Definition implements \M2E\Core\Model\Dashboard\Sales\DefinitionInterface
{
    private \M2E\TikTokShop\Model\Dashboard\Sales\DataProvider $dataProvider;

    public function __construct(
        \M2E\TikTokShop\Model\Dashboard\Sales\DataProvider $dataProvider
    ) {
        $this->dataProvider = $dataProvider;
    }
    public function getDataProvider(): \M2E\Core\Model\Dashboard\Sales\DataProviderInterface
    {
        return $this->dataProvider;
    }
}
