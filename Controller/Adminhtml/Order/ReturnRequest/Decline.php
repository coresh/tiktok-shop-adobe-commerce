<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order\ReturnRequest;

use M2E\TikTokShop\Model\Order\ReturnRequest\Decline\DeclineReason;
use M2E\TikTokShop\Model\Order\ReturnRequest\Decline\DeclineReasonCollection;

class Decline extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\ReturnRequest\Decline $declineReturnRequest;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\ReturnRequest\Decline $declineReturnRequest,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->declineReturnRequest = $declineReturnRequest;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        try {
            $order = $this->getOrderFromRequest($this->getRequest());
            $declineReasons = $this->getDeclineReasonFromRequest($this->getRequest());
            $declineResult = $this->declineReturnRequest
                ->process($order, $declineReasons, \M2E\Core\Helper\Data::INITIATOR_USER);
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(
                __("Buyer's return request was not declined. Reason: %reason", [
                    'reason' => $exception->getMessage(),
                ])
            );
            return $this->getResult();
        }

        if ($declineResult) {
            $this->messageManager->addSuccessMessage(__("Buyer's return request was declined."));
        } else {
            $this->messageManager->addErrorMessage(__("Buyer's return request was not declined."));
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

    private function getDeclineReasonFromRequest(\Magento\Framework\App\RequestInterface $request): DeclineReasonCollection
    {
        $jsonString = $request->getParam('decline_reasons');
        $declineReasons = json_decode($jsonString, true);

        if (!is_array($declineReasons) || empty($declineReasons)) {
            throw new \RuntimeException(
                (string)__('The request is missing one or more required parameters.')
            );
        }

        $reasons = [];
        foreach ($declineReasons as $reason) {
            $reasons[] = new DeclineReason(
                $reason['return_id'],
                $reason['reason']
            );
        }

        return new DeclineReasonCollection($reasons);
    }
}
