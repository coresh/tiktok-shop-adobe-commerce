<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

class ListingFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Listing
    {
        return $this->objectManager->create(Listing::class);
    }
}
