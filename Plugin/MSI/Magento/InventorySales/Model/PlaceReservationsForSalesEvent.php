<?php

namespace M2E\TikTokShop\Plugin\MSI\Magento\InventorySales\Model;

use M2E\TikTokShop\Model\MSI\Order\Reserve;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;

class PlaceReservationsForSalesEvent extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    private \M2E\TikTokShop\Model\MSI\AffectedProducts $msiAffectedProducts;
    private \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface $getStockByChannel;
    private \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\MSI\AffectedProducts $msiAffectedProducts,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $attributeTrackerFactory
    ) {
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->getStockByChannel = $objectManager->get(GetStockBySalesChannelInterface::class);
        $this->changeAttributeTrackerFactory = $attributeTrackerFactory;
        $this->listingLogService = $listingLogService;
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        /**
         * @var \Magento\InventorySalesApi\Api\Data\ItemToSellInterface[] $items
         * @var \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
         * @var \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent
         */
        [$items, $salesChannel, $salesEvent] = $arguments;

        $result = $callback(...$arguments);

        $stock = $this->getStockByChannel->execute($salesChannel);
        foreach ($items as $item) {
            $affectedProductCollection = $this->msiAffectedProducts->getAffectedProductsByStockAndSku(
                $stock->getStockId(),
                $item->getSku()
            );

            if ($affectedProductCollection->isEmpty()) {
                continue;
            }

            $this->addListingProductInstructions($affectedProductCollection);

            foreach ($affectedProductCollection->getProducts() as $affectedProduct) {
                $this->logListingProductMessage($affectedProduct, $salesEvent, $salesChannel, $item);
            }
        }

        return $result;
    }

    private function logListingProductMessage(
        \M2E\TikTokShop\Model\Product\AffectedProduct\Product $affectedProduct,
        \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent,
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel,
        \Magento\InventorySalesApi\Api\Data\ItemToSellInterface $item
    ): void {
        $qty = abs($item->getQuantity());
        $stock = $this->getStockByChannel->execute($salesChannel);

        switch ($salesEvent->getType()) {
            //region M2E TTS Reservation
            case Reserve::EVENT_TYPE_MAGENTO_RESERVATION_PLACED:
                $resultMessage = [
                    'string' => 'Product Quantity was reserved from the "%stock_name%" Stock ' .
                        'in the amount of %qty% by M2E Connect.',
                    'params' => [
                        '!stock_name' => $stock->getName(),
                        '!qty' => $qty
                    ],
                ];
                break;

            case Reserve::EVENT_TYPE_MAGENTO_RESERVATION_RELEASED:
                $resultMessage = [
                    'string' => 'Product Quantity reservation was released from the "%stock_name%" Stock ' .
                        'in the amount of %qty% by M2E Connect.',
                    'params' => [
                        '!stock_name' => $stock->getName(),
                        '!qty' => $qty
                    ],
                ];
                break;
            //endregion

            //region Order
            case SalesEventInterface::EVENT_ORDER_PLACED:
                $resultMessage = [
                    'string' => 'Product Quantity was reserved from the "%stock_name%" Stock ' .
                        'in the amount of %qty% because Magento Order was created.',
                    'params' => [
                        '!stock_name' => $stock->getName(),
                        '!qty' => $qty
                    ],
                ];
                break;

            case SalesEventInterface::EVENT_ORDER_PLACE_FAILED:
                $resultMessage = [
                    'string' => 'Product Quantity reservation was released from the "%stock_name%" Stock ' .
                        'in the amount of %qty% because Magento Order failed to be created.',
                    'params' => [
                        '!stock_name' => $stock->getName(),
                        '!qty' => $qty
                    ],
                ];
                break;

            case SalesEventInterface::EVENT_ORDER_CANCELED:
                $resultMessage = [
                    'string' => 'Product Quantity reservation was released from the "%stock_name%" Stock ' .
                        'in the amount of %qty% because Magento Order was canceled.',
                    'params' => [
                        '!stock_name' => $stock->getName(),
                        '!qty' => $qty
                    ],
                ];
                break;
            //endregion

            case SalesEventInterface::EVENT_SHIPMENT_CREATED:
                $resultMessage = [
                    'string' => 'Product Quantity reservation was released from the "%stock_name%" Stock ' .
                        'in the amount of %qty% because Magento Shipment was created.',
                    'params' => [
                        '!stock_name' => $stock->getName(),
                        '!qty' => $qty
                    ],
                ];
                break;

            case SalesEventInterface::EVENT_CREDITMEMO_CREATED:
                $resultMessage = [
                    'string' => 'Product Quantity was reserved from the "%stock_name%" Stock ' .
                        'in the amount of %qty% because Credit Memo was created.',
                    'params' => [
                        '!stock_name' => $stock->getName(),
                        '!qty' => $qty
                    ],
                ];
                break;

            default:
                $message = $item->getQuantity() > 0
                    ? 'Product Quantity reservation was released from the "%stock_name%" Stock ' .
                    'in the amount of %qty% because "%type%" event occurred.'
                    : 'Product Quantity was reserved from the "%stock_name%" Stock ' .
                    'in the amount of %qty% because "%type%" event occurred.';

                $resultMessage = [
                    'string' => $message,
                    'params' => [
                        '!stock_name' => $stock->getName(),
                        '!qty' => $qty,
                        '!type' => $salesEvent->getType()
                    ],
                ];
        }

        $this->listingLogService->addProduct(
            $affectedProduct->getProduct(),
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
            null,
            \M2E\TikTokShop\Helper\Module\Log::encodeDescription(
                $resultMessage['string'],
                $resultMessage['params']
            ),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function addListingProductInstructions(
        \M2E\TikTokShop\Model\Product\AffectedProduct\Collection $affectedProductCollection
    ) {
        foreach ($affectedProductCollection->getProducts() as $affectedProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $affectedProduct->getProduct(),
                $affectedProduct->getProduct()->getDescriptionTemplate()
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }
}
