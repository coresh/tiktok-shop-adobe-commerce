<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order\Cancellation;

class Decline extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\CancellationRequest\Decline $declineCancellationRequest;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\CancellationRequest\Decline $declineCancellationRequest,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->declineCancellationRequest = $declineCancellationRequest;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        try {
            $order = $this->getOrderFromRequest($this->getRequest());
            $declineReason = $this->getDeclineReasonFromRequest($this->getRequest());
            $declineResult = $this->declineCancellationRequest
                ->process($order, $declineReason, \M2E\Core\Helper\Data::INITIATOR_USER);
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(
                __('Order Cancellation Request was not declined. Reason: %reason', [
                    'reason' => $exception->getMessage(),
                ])
            );
            return $this->getResult();
        }

        if ($declineResult) {
            $this->messageManager->addSuccessMessage(__('Order Cancellation Request was declined.'));
        } else {
            $this->messageManager->addErrorMessage(__('Order Cancellation Request was not declined.'));
        }

        return $this->getResult();
    }

    private function getOrderFromRequest(
        \Magento\Framework\App\RequestInterface $request
    ): \M2E\TikTokShop\Model\Order {
        $orderId = $request->getParam('order_id');
        if (!$orderId) {
            throw new \RuntimeException(
                (string)__('The request is missing one or more required parameters.')
            );
        }

        $order = $this->orderRepository->find((int)$orderId);
        if ($order === null) {
            throw new \RuntimeException(
                (string)__('The specified order entity was not found.')
            );
        }

        return $order;
    }

    private function getDeclineReasonFromRequest(\Magento\Framework\App\RequestInterface $request): string
    {
        $declineReason = $request->getParam('decline_reason');
        if (!$declineReason) {
            throw new \RuntimeException(
                (string)__('The request is missing one or more required parameters.')
            );
        }

        return (string)$declineReason;
    }
}
