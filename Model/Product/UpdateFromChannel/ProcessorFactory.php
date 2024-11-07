<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product\UpdateFromChannel;

class ProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Listing\Other\TtsProduct $channelProduct
    ): Processor {
        return $this->objectManager->create(
            Processor::class,
            [
                'product' => $product,
                'channelProduct' => $channelProduct,
            ],
        );
    }
}
