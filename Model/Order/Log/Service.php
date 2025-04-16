<?php

namespace M2E\TikTokShop\Model\Order\Log;

class Service
{
    private int $initiator = \M2E\Core\Helper\Data::INITIATOR_EXTENSION;

    private \M2E\TikTokShop\Model\Order\LogFactory $orderLogFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Log $orderLogResource;
    private \M2E\TikTokShop\Model\OrderFactory $orderFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order $orderResource;
    private \M2E\TikTokShop\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollection;

    public function __construct(
        \M2E\TikTokShop\Model\Order\LogFactory $orderLogFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order\Log $orderLogResource,
        \M2E\TikTokShop\Model\OrderFactory $orderFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order $orderResource,
        \M2E\TikTokShop\Model\ResourceModel\Order\Log\CollectionFactory $orderLogCollection
    ) {
        $this->orderLogFactory = $orderLogFactory;
        $this->orderLogResource = $orderLogResource;
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
        $this->orderLogCollection = $orderLogCollection;
    }

    public function setInitiator(int $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function getInitiator(): ?int
    {
        return $this->initiator;
    }

    /**
     * @param \M2E\TikTokShop\Model\Order|int|string $order
     * @param string $description
     * @param int $type
     * @param array $additionalData
     * @param bool $isUnique
     *
     * @return bool
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function addMessage(
        $order,
        string $description,
        int $type,
        array $additionalData = [],
        bool $isUnique = false
    ): bool {
        if (!($order instanceof \M2E\TikTokShop\Model\Order)) {
            $order = $this->findOrder($order);
        }

        if ($order->isObjectNew()) {
            return false;
        }

        if (
            $isUnique
            && $this->isExist($order->getId(), $description)
        ) {
            return false;
        }

        $orderLog = $this->orderLogFactory->create();
        $orderLog->setAccountId($order->getAccountId());
        $orderLog->setShopId($order->getShopId());
        $orderLog->setOrderId($order->getId());
        $orderLog->setDescription($description);
        $orderLog->setType($type);
        $orderLog->setInitiator($this->getInitiator());
        $orderLog->setAdditionalData(\M2E\Core\Helper\Json::encode($additionalData));
        $orderLog->setCreateDate(\M2E\Core\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s'));

        $this->orderLogResource->save($orderLog);

        return true;
    }

    private function isExist(int $orderId, string $message): bool
    {
        $collection = $this->orderLogCollection->create();
        $collection->addFieldToFilter('order_id', $orderId);
        $collection->addFieldToFilter('description', $message);

        return ($collection->getSize() > 0);
    }

    private function findOrder(int $orderId): \M2E\TikTokShop\Model\Order
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId);

        return $order;
    }
}
