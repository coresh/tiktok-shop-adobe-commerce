<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Item;

class ProductAssignService
{
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param \M2E\TikTokShop\Model\Order\Item[] $orderItems
     * @param \Magento\Catalog\Model\Product $magentoProduct
     * @param int $initiator
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function assign(array $orderItems, \Magento\Catalog\Model\Product $magentoProduct, int $initiator): void
    {
        $loggedOrders = [];
        foreach ($orderItems as $orderItem) {
            $orderItem->setMagentoProductId((int)$magentoProduct->getId());
            $this->orderItemRepository->save($orderItem);

            if ($initiator === \M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION) {
                continue;
            }

            if (!isset($loggedOrders[$orderItem->getOrderId()])) {
                $orderItem->getOrder()->getLogService()->setInitiator($initiator);
                $orderItem->getOrder()->addSuccessLog(
                    'Order Item "%title%" was Linked.',
                    [
                        'title' => $orderItem->getChannelProductTitle(),
                    ]
                );
            }
            $loggedOrders[$orderItem->getOrderId()] = true;
        }
    }

    /**
     * @param \M2E\TikTokShop\Model\Order\Item[] $orderItems
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function unAssign(array $orderItems): void
    {
        $orders = [];
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getOrder()->getReserve()->isPlaced()) {
                $orderItem->getOrder()->getReserve()->cancel();
            }

            $orderItem->removeMagentoProductId();
            $orderItem->removeAssociatedProducts();
            $orderItem->removeAssociatedOptions();

            $this->orderItemRepository->save($orderItem);

            if (!isset($orders[$orderItem->getOrder()->getId()])) {
                $orderItem->getOrder()->getLogService()->setInitiator(\M2E\TikTokShop\Helper\Data::INITIATOR_USER);
                $orderItem->getOrder()->addSuccessLog(
                    'Item "%title%" was Unlinked.',
                    [
                        'title' => $orderItem->getChannelProductTitle(),
                    ]
                );
            }
            $orders[$orderItem->getOrder()->getId()] = true;
        }
    }
}
