<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Inventory;

use M2E\TikTokShop\Model\Magento\Product\Inventory;
use M2E\TikTokShop\Model\MSI\Magento\Product\Inventory as MSIInventory;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

class Factory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\TikTokShop\Helper\Magento $magentoHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Helper\Magento $magentoHelper
    ) {
        $this->objectManager = $objectManager;
        $this->magentoHelper = $magentoHelper;
    }

    public function getObject(
        \Magento\Catalog\Model\Product $product
    ): \M2E\TikTokShop\Model\Magento\Product\Inventory\AbstractModel {
        /** @var \M2E\TikTokShop\Model\Magento\Product\Inventory\AbstractModel $object */
        $object = $this->objectManager->get($this->isMsiMode($product) ? MSIInventory::class : Inventory::class);
        $object->setProduct($product);

        return $object;
    }

    private function isMsiMode(\Magento\Catalog\Model\Product $product): bool
    {
        if (!$this->magentoHelper->isMSISupportingVersion()) {
            return false;
        }

        if (interface_exists(IsSourceItemManagementAllowedForProductTypeInterface::class)) {
            $isSourceItemManagementAllowedForProductType = $this->objectManager->get(
                IsSourceItemManagementAllowedForProductTypeInterface::class,
            );

            return $isSourceItemManagementAllowedForProductType->execute($product->getTypeId());
        }

        return true;
    }
}
