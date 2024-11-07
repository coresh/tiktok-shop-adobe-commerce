<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat;

use M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat\Collection as TemplateSellingFormatCollection;

class CollectionFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): TemplateSellingFormatCollection
    {
        return $this->objectManager->create(TemplateSellingFormatCollection::class);
    }
}
