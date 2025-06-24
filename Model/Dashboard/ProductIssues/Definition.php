<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\ProductIssues;

class Definition implements \M2E\Core\Model\Dashboard\ProductIssues\DefinitionInterface
{
    private \M2E\TikTokShop\Model\Dashboard\ProductIssues\DataProvider $dataProvider;
    private \M2E\TikTokShop\Model\Dashboard\ProductIssues\InfoProvider $infoProvider;

    public function __construct(
        \M2E\TikTokShop\Model\Dashboard\ProductIssues\DataProvider $dataProvider,
        \M2E\TikTokShop\Model\Dashboard\ProductIssues\InfoProvider $infoProvider
    ) {
        $this->infoProvider = $infoProvider;
        $this->dataProvider = $dataProvider;
    }
    public function getDataProvider(): \M2E\Core\Model\Dashboard\ProductIssues\DataProviderInterface
    {
        return $this->dataProvider;
    }

    public function getInfoProvider(): \M2E\Core\Model\Dashboard\ProductIssues\InfoProviderInterface
    {
        return $this->infoProvider;
    }
}
