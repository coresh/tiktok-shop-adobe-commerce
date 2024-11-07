<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

use M2E\TikTokShop\Controller\Adminhtml\AbstractOrder;

class ReservationPlace extends AbstractOrder
{
    public function execute()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->messageManager->addError(__('Please select Order(s).'));
            $this->_redirect('*/*/index');

            return;
        }

        /** @var \M2E\TikTokShop\Model\Order[] $orders */
        $orders = $this->activeRecordFactory->getObject('Order')
                                            ->getCollection()
                                            ->addFieldToFilter('id', ['in' => $ids])
                                            ->addFieldToFilter(
                                                'reservation_state',
                                                ['neq' => \M2E\TikTokShop\Model\Order\Reserve::STATE_PLACED]
                                            )
                                            ->addFieldToFilter('magento_order_id', ['null' => true]);

        try {
            $actionSuccessful = false;

            foreach ($orders as $order) {
                $order->getLogService()->setInitiator(\M2E\TikTokShop\Helper\Data::INITIATOR_USER);

                if (!$order->isReservable()) {
                    continue;
                }

                if ($order->getReserve()->place()) {
                    $actionSuccessful = true;
                }
            }

            if ($actionSuccessful) {
                $this->messageManager->addSuccess(
                    __('QTY for selected Order(s) was reserved.')
                );
            } else {
                $this->messageManager->addError(
                    __('QTY for selected Order(s) was not reserved.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __(
                    'QTY for selected Order(s) was not reserved. Reason: %error_message',
                    ['error_message' => $e->getMessage()],
                )
            );
        }

        $this->_redirect($this->redirect->getRefererUrl());
    }
}
