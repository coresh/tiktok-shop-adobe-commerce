<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Template\Compliance;

use M2E\TikTokShop\Model\ResourceModel\Template\Compliance\Collection as TemplateDescriptionCollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): TemplateDescriptionCollection
    {
        return $this->objectManager->create(TemplateDescriptionCollection::class);
    }
}
