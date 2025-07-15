<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order\ReturnRequest;

class Accept extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\ReturnRequest\Accept $acceptReturnRequest;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\ReturnRequest\Accept $acceptReturnRequest,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        $context = null
    ) {
        parent::__construct($context);
        $this->acceptReturnRequest = $acceptReturnRequest;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
    }

    public function execute()
    {
        try {
            $order = $this->getOrderFromRequest($this->getRequest());
            $result = $this->acceptReturnRequest
                ->process($order, \M2E\Core\Helper\Data::INITIATOR_USER);
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(
                __('Order return request was not approved. Reason: %reason', [
                    'reason' => $exception->getMessage(),
                ])
            );
            return $this->getResult();
        }

        if ($result) {
            $this->messageManager->addSuccessMessage(__('Order return request approved by seller; awaiting buyer shipment.'));
        } else {
            $this->messageManager->addErrorMessage(__('Order return request was not approved.'));
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
}
