<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento;

class ProductFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Product
    {
        return $this->objectManager->create(Product::class);
    }

    public function createByProductId(int $productId): Product
    {
        return $this->createInstance(['productId' => $productId]);
    }

    public function createInstance(array $params): Product
    {
        return $this->objectManager->create(Product::class, $params);
    }
}
