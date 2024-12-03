<?php

namespace M2E\TikTokShop\Model\Template\Compliance;

use M2E\TikTokShop\Model\Template\Compliance\ChangeProcessor;

class ChangeProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): ChangeProcessor
    {
        return $this->objectManager->create(ChangeProcessor::class);
    }
}
