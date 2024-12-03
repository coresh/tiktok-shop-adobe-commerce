<?php

namespace M2E\TikTokShop\Model\Template\Compliance;

use M2E\TikTokShop\Model\Template\Compliance\Diff;

class DiffFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Diff
    {
        return $this->objectManager->create(Diff::class);
    }
}
