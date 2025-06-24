<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Products;

class Definition implements \M2E\Core\Model\Dashboard\Products\DefinitionInterface
{
    private \M2E\TikTokShop\Model\Dashboard\Products\DataProvider $dataProvider;

    public function __construct(
        \M2E\TikTokShop\Model\Dashboard\Products\DataProvider $dataProvider
    ) {
        $this->dataProvider = $dataProvider;
    }
    public function getDataProvider(): \M2E\Core\Model\Dashboard\Products\DataProviderInterface
    {
        return $this->dataProvider;
    }
}
