<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Template\Description;

use M2E\TikTokShop\Model\ResourceModel\Template\Description\Collection as TemplateDescriptionCollection;

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
