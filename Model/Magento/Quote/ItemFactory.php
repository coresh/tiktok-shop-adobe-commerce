<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Quote;

class ItemFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \Magento\Quote\Model\Quote $quote,
        \M2E\TikTokShop\Model\Order\Item\ProxyObject $proxyItem
    ): Item {
        return $this->objectManager->create(
            Item::class,
            [
                'quote' => $quote,
                'proxyItem' => $proxyItem,
            ],
        );
    }
}
