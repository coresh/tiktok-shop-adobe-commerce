<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Order;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder;

class CreateMagentoOrder extends AbstractOrder
{
    private \M2E\TikTokShop\Model\OrderFactory $orderFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order $orderResource;

    public function __construct(
        \M2E\TikTokShop\Model\OrderFactory $orderFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order $orderResource
    ) {
        parent::__construct();
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
    }

    public function execute()
    {
        $orderIds = $this->getRequestIds();

        $errors = 0;

        foreach ($orderIds as $orderId) {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, (int)$orderId);
            $order->getLogService()->setInitiator(\M2E\TikTokShop\Helper\Data::INITIATOR_USER);

            // Create magento order
            // ---------------------------------------
            try {
                $order->createMagentoOrder();
            } catch (\Exception $e) {
                $errors++;
            }

            // ---------------------------------------

            if ($order->canCreateInvoice()) {
                $order->createInvoice();
            }

            $order->createShipments();

            if ($order->canCreateTracks()) {
                $order->createTracks();
            }
        }

        if (!$errors) {
            $this->messageManager->addSuccess(__('Magento Order(s) were created.'));
        }

        if ($errors) {
            $this->messageManager->addError(
                __(
                    '%count Magento order(s) were not created. Please ' .
                    '<a target="_blank" href="%url">view Log</a> for the details.',
                    [
                        'count' => $errors,
                        'url' => $this->getUrl('*/tiktokshop_log_order')
                    ]
                )
            );
        }

        if (count($orderIds) == 1) {
            return $this->_redirect('*/*/view', ['id' => $orderIds[0]]);
        } else {
            return $this->_redirect($this->redirect->getRefererUrl());
        }
    }
}
