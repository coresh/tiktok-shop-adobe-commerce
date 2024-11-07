<?php

namespace M2E\TikTokShop\Model\Order;

use M2E\TikTokShop\Model\ResourceModel\Order\Change as OrderChangeResource;

class Change extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const ACTION_UPDATE_SHIPPING = 'update_shipping';
    public const ACTION_CANCEL = 'cancel';

    public const MAX_ALLOWED_PROCESSING_ATTEMPTS = 3;

    private \M2E\TikTokShop\Model\Order $order;
    /** @var \M2E\TikTokShop\Model\Order\Repository */
    private Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->orderRepository = $orderRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(OrderChangeResource::class);
    }

    public function init(string $hash, int $orderId, string $action, $creatorType, array $params): self
    {
        if (!in_array($action, [self::ACTION_UPDATE_SHIPPING, self::ACTION_CANCEL])) {
            throw new \InvalidArgumentException('Action is invalid.');
        }

        $this->setData([
            OrderChangeResource::COLUMN_ORDER_ID => $orderId,
            OrderChangeResource::COLUMN_ACTION => $action,
            OrderChangeResource::COLUMN_PARAMS => json_encode($params, JSON_THROW_ON_ERROR),
            OrderChangeResource::COLUMN_CREATOR_TYPE => $creatorType,
            OrderChangeResource::COLUMN_HASH => $hash,
        ]);

        return $this;
    }

    // ----------------------------------------

    public function getOrder(): \M2E\TikTokShop\Model\Order
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->order)) {
            return $this->order;
        }

        return $this->order = $this->orderRepository->get($this->getOrderId());
    }

    public function getOrderId(): int
    {
        return (int)$this->getData(OrderChangeResource::COLUMN_ORDER_ID);
    }

    public function isShippingUpdateAction(): bool
    {
        return $this->getAction() === self::ACTION_UPDATE_SHIPPING;
    }

    public function getAction(): string
    {
        return $this->getData(OrderChangeResource::COLUMN_ACTION);
    }

    public function getCreatorType(): int
    {
        return (int)$this->getData(OrderChangeResource::COLUMN_CREATOR_TYPE);
    }

    public function setParams(array $params): void
    {
        $this->setData(OrderChangeResource::COLUMN_PARAMS, json_encode($params));
    }

    public function getParams(): array
    {
        $params = $this->getData(OrderChangeResource::COLUMN_PARAMS);
        if (empty($params)) {
            return [];
        }

        return json_decode($params, true);
    }

    public function getOrderItemsIdsForShipping(): array
    {
        $result = [];
        foreach ($this->getParams()['items'] ?? [] as $itemsData) {
            $result[] = $itemsData['item_id'];
        }

        return $result;
    }

    public function getHash(): string
    {
        return $this->getData(OrderChangeResource::COLUMN_HASH);
    }

    public function incrementAttempts(): void
    {
        $this->setData(OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT, $this->getAttemptsCount() + 1);
        $this->setData(
            OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_DATE,
            \M2E\TikTokShop\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s')
        );
    }

    public function getAttemptsCount(): int
    {
        return (int)$this->getData(OrderChangeResource::COLUMN_PROCESSING_ATTEMPT_COUNT);
    }
}
