<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\UnmanagedProduct\VariantSku;

class SalesAttributeFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        array $salesAttributeData
    ): SalesAttribute {
        return $this->objectManager->create(
            SalesAttribute::class,
            [
                'name' => $salesAttributeData['name'],
                'valueName' => $salesAttributeData['value_name'],
            ],
        );
    }
}
