<?php

namespace M2E\TikTokShop\Model\Template\SellingFormat;

class AffectedListingsProductsFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): AffectedListingsProducts
    {
        return $this->objectManager->create(AffectedListingsProducts::class);
    }
}
