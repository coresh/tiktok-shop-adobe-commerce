<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

use M2E\TikTokShop\Controller\Adminhtml\AbstractOrder;

class ReservationCancel extends AbstractOrder
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
                                                \M2E\TikTokShop\Model\Order\Reserve::STATE_PLACED
                                            );

        try {
            $actionSuccessful = false;

            foreach ($orders as $order) {
                $order->getLogService()->setInitiator(\M2E\TikTokShop\Helper\Data::INITIATOR_USER);

                if ($order->getReserve()->cancel()) {
                    $actionSuccessful = true;
                    $order->getReserve()->addSuccessLogCancelQty();
                }
            }

            if ($actionSuccessful) {
                $this->messageManager->addSuccess(
                    __('QTY reserve for selected Order(s) was canceled.')
                );
            } else {
                $this->messageManager->addError(
                    __('QTY reserve for selected Order(s) was not canceled.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __(
                    'QTY reserve for selected Order(s) was not canceled. Reason: %error_message',
                    ['error_message' => $e->getMessage()],
                )
            );
        }

        $this->_redirect($this->redirect->getRefererUrl());
    }
}
