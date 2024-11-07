<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other\Updater;

class ServerToTtsProductConverterFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ): ServerToTtsProductConverter {
        return $this->objectManager->create(
            ServerToTtsProductConverter::class,
            [
                'account' => $account,
                'shop' => $shop,
            ],
        );
    }
}
