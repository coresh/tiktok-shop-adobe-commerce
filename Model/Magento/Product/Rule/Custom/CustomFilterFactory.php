<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Custom;

class CustomFilterFactory
{
    private array $customFiltersMap = [
        Magento\Qty::NICK => Magento\Qty::class,
        Magento\Stock::NICK => Magento\Stock::class,
        Magento\TypeId::NICK => Magento\TypeId::class,
        TikTokShop\ProductId::NICK => TikTokShop\ProductId::class,
        TikTokShop\OnlineCategory::NICK => TikTokShop\OnlineCategory::class,
        TikTokShop\OnlineTitle::NICK => TikTokShop\OnlineTitle::class,
        TikTokShop\OnlineQty::NICK => TikTokShop\OnlineQty::class,
        TikTokShop\OnlineSku::NICK => TikTokShop\OnlineSku::class,
        TikTokShop\OnlinePrice::NICK => TikTokShop\OnlinePrice::class,
        TikTokShop\Status::NICK => TikTokShop\Status::class,
    ];

    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createByType(string $type): \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
    {
        $filterClass = $this->choiceCustomFilterClass($type);
        if ($filterClass === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                sprintf('Unknown custom filter - %s', $type)
            );
        }

        return $this->objectManager->create($filterClass);
    }

    private function choiceCustomFilterClass(string $type): ?string
    {
        return $this->customFiltersMap[$type] ?? null;
    }
}
