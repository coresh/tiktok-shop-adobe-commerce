<?php

namespace M2E\TikTokShop\Model\MSI\Magento\Order;

use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface as isSourceManagement;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface as DefaultAlgorithm;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;

class Shipment extends \M2E\TikTokShop\Model\Magento\Order\Shipment
{
    /** @var StockByWebsiteIdResolverInterface */
    private $stockByWebsiteIdResolver;

    /** @var DefaultAlgorithm */
    private $algorithm;

    /**@var SourceSelectionServiceInterface */
    private $sourceSelectionService;

    /**@var ShipmentItemCreationInterfaceFactory */
    private $itemCreationFactory;

    /** @var ShipmentExtensionFactory */
    private $shipmentExtensionFactory;

    /** @var ItemRequestInterfaceFactory */
    private $itemRequestFactory;

    /** @var InventoryRequestInterfaceFactory */
    private $inventoryRequestFactory;

    /** @var isSourceManagement */
    private $isSourceItemManagement;

    //########################################
    private \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \M2E\TikTokShop\Observer\Shipment\EventRuntimeManager $eventRuntimeManager
    ) {
        parent::__construct(
            $shipmentDocumentFactory,
            $transactionFactory,
            $eventRuntimeManager
        );

        $this->itemRequestFactory = $objectManager->get(ItemRequestInterfaceFactory::class);
        $this->inventoryRequestFactory = $objectManager->get(InventoryRequestInterfaceFactory::class);
        $this->stockByWebsiteIdResolver = $objectManager->get(StockByWebsiteIdResolverInterface::class);
        $this->algorithm = $objectManager->get(DefaultAlgorithm::class);
        $this->sourceSelectionService = $objectManager->get(SourceSelectionServiceInterface::class);
        $this->itemCreationFactory = $objectManager->get(ShipmentItemCreationInterfaceFactory::class);
        $this->shipmentExtensionFactory = $objectManager->get(ShipmentExtensionFactory::class);
        $this->isSourceItemManagement = $objectManager->get(isSourceManagement::class);
        $this->magentoProductHelper = $magentoProductHelper;
    }

    //########################################

    protected function prepareShipments()
    {
        $selectionRequestItems = [];
        $orderItemIdsBySku = [];

        foreach ($this->magentoOrder->getAllItems() as $item) {
            $qtyToShip = $item->getQtyToShip();
            if ($qtyToShip == 0) {
                continue;
            }

            /**
             * Magento interface do not support situation when a bundle product
             * with the parameter "Ship Bundle Items" == "Together" is in one order with products
             * with more then 1 Source
             */
            if (
                $this->magentoProductHelper->isBundleType($item->getProductType()) &&
                !$item->isShipSeparately()
            ) {
                throw new \M2E\TikTokShop\Model\Exception\Logic(
                    'Shipping Bundle items together is not supported by Magento in Multi Source mode.'
                );
            }

            $selectionRequestItems[] = $this->itemRequestFactory->create([
                'sku' => $item->getSku(),
                'qty' => $qtyToShip,
            ]);

            $orderItemIdsBySku[$item->getSku()] = $item->getItemId();
        }

        if (empty($selectionRequestItems) || empty($orderItemIdsBySku)) {
            return;
        }

        $websiteId = (int)$this->magentoOrder->getStore()->getWebsiteId();

        /** @var \Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface $inventoryRequest */
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $this->stockByWebsiteIdResolver->execute($websiteId)->getStockId(),
            'items' => $selectionRequestItems,
        ]);

        $selectionAlgorithmCode = $this->algorithm->execute();
        $sourceSelectionResult = $this->sourceSelectionService->execute($inventoryRequest, $selectionAlgorithmCode);

        $itemsPerSourceCode = [];

        foreach ($sourceSelectionResult->getSourceSelectionItems() as $sourceSelectionItem) {
            if ($sourceSelectionItem->getQtyToDeduct() <= 0) {
                continue;
            }
            $shipmentItem = $this->itemCreationFactory->create();
            $shipmentItem->setQty($sourceSelectionItem->getQtyToDeduct());
            $shipmentItem->setOrderItemId($orderItemIdsBySku[$sourceSelectionItem->getSku()]);
            $itemsPerSourceCode[$sourceSelectionItem->getSourceCode()][] = $shipmentItem;
        }

        /**
         * The track number of only one, last shipment is sent to Channel.
         * When creating more then one shipments for one order, problems may arise.
         */
        foreach ($itemsPerSourceCode as $sourceCode => $shipmentItems) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $this->shipmentDocumentFactory->create($this->magentoOrder, $shipmentItems);
            $shipmentExtension = $this->shipmentExtensionFactory->create();
            $shipmentExtension->setSourceCode((string)$sourceCode);
            $shipment->setExtensionAttributes($shipmentExtension);
            $shipment->register();

            $this->shipments[] = $shipment;
        }
    }
}
