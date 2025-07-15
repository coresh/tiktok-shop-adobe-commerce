<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order\ReturnRequest;

class GetDeclineReasons extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;
    private \M2E\TikTokShop\Model\Order\ReturnRequest\DeclineReasons $declineReason;

    public function __construct(
        \M2E\TikTokShop\Model\Order\ReturnRequest\DeclineReasons $declineReason,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->declineReason = $declineReason;
    }

    public function execute()
    {
        $order = $this->getOrderFromRequest($this->getRequest());
        $reasonsByReturnId = $this->declineReason->getDeclineReasonsByReturnId($order);
        $titlesByReturnId = $this->mapReturnIdsToTitles($order);
        $groupedReasons = $this->formatGroupedDeclineReasons($titlesByReturnId, $reasonsByReturnId);

        $this->setJsonContent([
            'success' => true,
            'reasons' => $groupedReasons,
        ]);

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

    /**
     * @param \M2E\TikTokShop\Model\Order $order
     * @return array<string, string>
     */
    private function mapReturnIdsToTitles(\M2E\TikTokShop\Model\Order $order): array
    {
        $map = [];

        foreach ($order->getItems() as $item) {
            if ($item->hasRefundReturnId()) {
                $map[$item->getRefundReturnId()] = $item->getChannelProductTitle();
            }
        }

        return $map;
    }

    /**
     * @param array<string, string> $titlesByReturnId
     * @param \M2E\TikTokShop\Model\Channel\Order\ReturnRequest\DeclineReason[] $reasonsByReturnId
     *
     * @return array<string, array<string, array<array{name: string, text: string}>>>
     */
    private function formatGroupedDeclineReasons(array $titlesByReturnId, array $reasonsByReturnId): array
    {
        $result = [];

        foreach ($reasonsByReturnId as $returnId => $reasons) {
            if (!isset($titlesByReturnId[$returnId])) {
                continue;
            }

            $title = $titlesByReturnId[$returnId];

            foreach ($reasons as $reason) {
                $result[$title][$returnId][] = [
                    'name' => $reason->getName(),
                    'text' => $reason->getText(),
                ];
            }
        }

        return $result;
    }
}
