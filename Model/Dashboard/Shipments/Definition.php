<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Shipments;

class Definition implements \M2E\Core\Model\Dashboard\Shipments\DefinitionInterface
{
    private \M2E\TikTokShop\Model\Dashboard\Shipments\DataProvider $dataProvider;
    private \M2E\TikTokShop\Model\Dashboard\Shipments\InfoProvider $infoProvider;

    public function __construct(
        \M2E\TikTokShop\Model\Dashboard\Shipments\DataProvider $dataProvider,
        \M2E\TikTokShop\Model\Dashboard\Shipments\InfoProvider $infoProvider
    ) {
        $this->dataProvider = $dataProvider;
        $this->infoProvider = $infoProvider;
    }
    public function getDataProvider(): \M2E\Core\Model\Dashboard\Shipments\DataProviderInterface
    {
        return $this->dataProvider;
    }

    public function getInfoProvider(): \M2E\Core\Model\Dashboard\Shipments\InfoProviderInterface
    {
        return $this->infoProvider;
    }
}
