<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Order;

class UpdateShippingStatus extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder
{
    private \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory;
    private \M2E\TikTokShop\Model\Order\ShipmentService $orderShipmentHandler;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\ShipmentService $orderShipmentHandler,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $orderShipmentCollectionFactory,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository
    ) {
        parent::__construct();

        $this->orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->orderShipmentHandler = $orderShipmentHandler;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function execute()
    {
        $orderIds = $this->getRequestIds();

        if (count($orderIds) == 0) {
            $this->messageManager->addError(__('Please select Order(s).'));

            return false;
        }

        $orders = $this->orderRepository->findByIds($orderIds);

        $hasFailed = false;
        $hasSucceeded = false;

        foreach ($orders as $order) {
            $shipmentsCollection = $this->orderShipmentCollectionFactory->create();
            $shipmentsCollection->setOrderFilter($order->getMagentoOrderId());

            if ($shipmentsCollection->getSize() === 0) {
                $hasFailed = true;
                continue;
            }

            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            foreach ($shipmentsCollection->getItems() as $shipment) {
                if (!$shipment->getId()) {
                    continue;
                }

                $result = $this->orderShipmentHandler->shipByShipment(
                    $order,
                    $shipment,
                    \M2E\TikTokShop\Helper\Data::INITIATOR_USER
                );

                $result === \M2E\TikTokShop\Model\Order\ShipmentService::HANDLE_RESULT_SUCCEEDED
                    ? $hasSucceeded = true
                    : $hasFailed = true;
            }
        }

        if (!$hasFailed && $hasSucceeded) {
            $this->messageManager->addSuccess(
                __('Updating Order(s) Status to Shipped in Progress...')
            );
        } elseif ($hasFailed && !$hasSucceeded) {
            $this->messageManager->addError(
                __('Order(s) can not be updated for Shipped Status.')
            );
        } elseif ($hasFailed && $hasSucceeded) {
            $this->messageManager->addError(
                __('Some of Order(s) can not be updated for Shipped Status.')
            );
        }

        return $this->_redirect($this->redirect->getRefererUrl());
    }
}
