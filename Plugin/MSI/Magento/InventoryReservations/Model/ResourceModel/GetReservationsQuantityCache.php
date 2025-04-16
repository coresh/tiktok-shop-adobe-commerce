<?php

namespace M2E\TikTokShop\Plugin\MSI\Magento\InventoryReservations\Model\ResourceModel;

use Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity;

/**
 * Class \M2E\TikTokShop\Plugin\MSI\Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantityCache
 */
class GetReservationsQuantityCache extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    //########################################

    /** @var GetReservationsQuantity */
    private $getReservationsQuantity;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->getReservationsQuantity = $objectManager->get(GetReservationsQuantity::class);
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    public function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        [$sku, $stockId] = $arguments;
        $key = 'released_reservation_product_' . $sku . '_' . $stockId;

        /** @var \M2E\TikTokShop\Helper\Data\GlobalData $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Data\GlobalData::class
        );

        if ($helper->getValue($key)) {
            return $this->getReservationsQuantity->execute($sku, $stockId);
        }

        return $callback(...$arguments);
    }

    //########################################
}
