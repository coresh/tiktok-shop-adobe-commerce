<?php

namespace M2E\TikTokShop\Model\Magento\Order;

class Shipment
{
    protected \Magento\Sales\Model\Order $magentoOrder;

    /** @var \Magento\Sales\Model\Order\Shipment[] */
    protected array $shipments = [];

    // ---------------------------------------

    protected \Magento\Framework\DB\TransactionFactory $transactionFactory;
    protected \M2E\TikTokShop\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory;
    private \M2E\TikTokShop\Observer\Shipment\EventRuntimeManager $eventRuntimeManager;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \M2E\TikTokShop\Observer\Shipment\EventRuntimeManager $eventRuntimeManager
    ) {
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->transactionFactory = $transactionFactory;
        $this->eventRuntimeManager = $eventRuntimeManager;
    }

    //########################################

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     *
     * @return $this
     */
    public function setMagentoOrder(\Magento\Sales\Model\Order $magentoOrder)
    {
        $this->magentoOrder = $magentoOrder;

        return $this;
    }

    public function getShipments()
    {
        return $this->shipments;
    }

    public function buildShipments()
    {
        $this->prepareShipments();

        $this->eventRuntimeManager->skipEvents();

        $transaction = $this->transactionFactory->create();
        foreach ($this->shipments as $shipment) {
            // it is necessary for updating qty_shipped field in sales_flat_order_item table
            $shipment->getOrder()->setIsInProcess(true);

            $transaction->addObject($shipment);
            $transaction->addObject($shipment->getOrder());

            $this->magentoOrder->getShipmentsCollection()->addItem($shipment);
        }

        try {
            $transaction->save();
        } catch (\Exception $e) {
            $this->magentoOrder->getShipmentsCollection()->clear();
            throw $e;
        }

        $this->eventRuntimeManager->doNotSkipEvents();
    }

    protected function prepareShipments()
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->shipmentDocumentFactory->create($this->magentoOrder);
        $shipment->register();

        $this->shipments[] = $shipment;
    }
}
