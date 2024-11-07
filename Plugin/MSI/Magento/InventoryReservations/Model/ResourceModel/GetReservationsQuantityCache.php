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
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($helperFactory);

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
        if ($this->getHelper('Data\GlobalData')->getValue($key)) {
            return $this->getReservationsQuantity->execute($sku, $stockId);
        }

        return $callback(...$arguments);
    }

    //########################################
}
