<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order;

class EventDispatcher
{
    private const CHANEL_NAME = 'tts';

    private const REGION_AMERICA = 'america';
    private const REGION_EUROPE = 'europe';

    private \Magento\Framework\Event\ManagerInterface $eventManager;

    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function dispatchEventsMagentoOrderCreated(\M2E\TikTokShop\Model\Order $order): void
    {
        $this->eventManager->dispatch('m2e_tts_order_place_success', ['order' => $order]);

        $this->eventManager->dispatch('ess_magento_order_created', [
            'channel' => self::CHANEL_NAME,
            'channel_order_id' => (int)$order->getId(),
            'channel_external_order_id' => $order->getTtsOrderId(),
            'magento_order_id' => (int)$order->getMagentoOrderId(),
            'magento_order_increment_id' => $order->getMagentoOrder()->getIncrementId(),
            'channel_purchase_date' => $this->getPurchaseDate($order),
            'region' => $this->resolveRegion($order->getShop()),
        ]);
    }

    public function dispatchEventInvoiceCreated(\M2E\TikTokShop\Model\Order $order): void
    {
        $this->eventManager->dispatch('ess_order_invoice_created', [
            'channel' => self::CHANEL_NAME,
            'channel_order_id' => (int)$order->getId(),
        ]);
    }

    private function getPurchaseDate(\M2E\TikTokShop\Model\Order $order): \DateTime
    {
        return \M2E\TikTokShop\Helper\Date::createDateGmt(
            $order->getPurchaseCreateDate()
        );
    }

    private function resolveRegion(\M2E\TikTokShop\Model\Shop $shop): string
    {
        if ($shop->getRegion()->isUS()) {
            return self::REGION_AMERICA;
        }

        return self::REGION_EUROPE;
    }
}
