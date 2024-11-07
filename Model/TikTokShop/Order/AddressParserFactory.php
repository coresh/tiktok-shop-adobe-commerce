<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order;

class AddressParserFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        string $region,
        array $serverData
    ): \M2E\TikTokShop\Model\TikTokShop\Order\AbstractAddressParser {
        $arguments = [
            'serverData' => $serverData,
        ];

        if ($region === \M2E\TikTokShop\Model\Shop::REGION_GB) {
            return $this->objectManager
                ->create(\M2E\TikTokShop\Model\TikTokShop\Order\AddressParser\GB::class, $arguments);
        }

        if ($region === \M2E\TikTokShop\Model\Shop::REGION_US) {
            return $this->objectManager
                ->create(\M2E\TikTokShop\Model\TikTokShop\Order\AddressParser\US::class, $arguments);
        }

        if ($region === \M2E\TikTokShop\Model\Shop::REGION_ES) {
            return $this->objectManager
                ->create(\M2E\TikTokShop\Model\TikTokShop\Order\AddressParser\ES::class, $arguments);
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown region');
    }
}
