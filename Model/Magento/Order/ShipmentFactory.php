<?php

namespace M2E\TikTokShop\Model\Magento\Order;

use M2E\TikTokShop\Model\MSI\Magento\Order\Shipment as MSIShipment;
use Magento\InventoryShippingAdminUi\Model\IsOrderSourceManageable;
use Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Class \M2E\TikTokShop\Model\Magento\Order\ShipmentFactory
 */
class ShipmentFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    //########################################

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    //########################################

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param array $data
     *
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function create(\Magento\Sales\Api\Data\OrderInterface $order, array $data = [])
    {
        return $this->isMsiMode($order)
            ? $this->objectManager->create(MSIShipment::class, $data)
            : $this->objectManager->create(Shipment::class, $data);
    }

    //########################################

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return bool
     */
    private function isMsiMode(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        /** @var \M2E\Core\Helper\Magento $helper */
        $helper = $this->objectManager->get(\M2E\Core\Helper\Magento::class);

        if (!$helper->isMSISupportingVersion()) {
            return false;
        }

        $websiteId = (int)$order->getStore()->getWebsiteId();

        return $this->objectManager->get(IsWebsiteInMultiSourceMode::class)->execute($websiteId) &&
            $this->isOrderSourceManageable($order);
    }

    private function isOrderSourceManageable(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if (class_exists(IsOrderSourceManageable::class)) {
            return $this->objectManager->get(IsOrderSourceManageable::class)->execute($order);
        }

        $stocks = $this->objectManager->get(StockRepositoryInterface::class)->getList()->getItems();
        $orderItems = $order->getItems();
        foreach ($orderItems as $orderItem) {
            $isSourceItemManagementAllowed = $this->objectManager->get(
                IsSourceItemManagementAllowedForProductTypeInterface::class
            );

            if (!$isSourceItemManagementAllowed->execute($orderItem->getProductType())) {
                continue;
            }

            /** @var \Magento\InventoryApi\Api\Data\StockInterface $stock */
            foreach ($stocks as $stock) {
                $inventoryConfiguration = $this->objectManager->get(GetStockItemConfigurationInterface::class)->execute(
                    $this->objectManager->get(GetSkuFromOrderItemInterface::class)->execute($orderItem),
                    $stock->getStockId()
                );

                if ($inventoryConfiguration->isManageStock()) {
                    return true;
                }
            }
        }

        return false;
    }

    //########################################
}
