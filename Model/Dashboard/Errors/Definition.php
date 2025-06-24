<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\Errors;

class Definition implements \M2E\Core\Model\Dashboard\Errors\DefinitionInterface
{
    private \M2E\TikTokShop\Model\Dashboard\Errors\DataProvider $dataProvider;
    private \M2E\TikTokShop\Model\Dashboard\Errors\InfoProvider $infoProvider;

    public function __construct(
        \M2E\TikTokShop\Model\Dashboard\Errors\DataProvider $dataProvider,
        \M2E\TikTokShop\Model\Dashboard\Errors\InfoProvider $infoProvider
    ) {
        $this->dataProvider = $dataProvider;
        $this->infoProvider = $infoProvider;
    }
    public function getDataProvider(): \M2E\Core\Model\Dashboard\Errors\DataProviderInterface
    {
        return $this->dataProvider;
    }

    public function getInfoProvider(): \M2E\Core\Model\Dashboard\Errors\InfoProviderInterface
    {
        return $this->infoProvider;
    }
}
