<?php

namespace M2E\TikTokShop\Model\Template\Compliance;

use M2E\TikTokShop\Model\Template\Compliance\AffectedListingsProducts;

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
