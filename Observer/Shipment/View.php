<?php

namespace M2E\TikTokShop\Observer\Shipment;

class View extends \M2E\TikTokShop\Observer\AbstractObserver
{
    /** @var \Magento\Customer\Model\CustomerFactory */
    protected $customerFactory;
    /** @var \Magento\Framework\Registry */
    protected $registry;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Registry $registry,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct($helperFactory);
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
    }

    //########################################

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->registry->registry('current_shipment');
        if (empty($shipment) || !$shipment->getId()) {
            return;
        }

        try {
            $order = $this->orderRepository->findByMagentoOrderId($shipment->getOrderId());
        } catch (\Exception $exception) {
            return;
        }

        if (empty($order) || !$order->getId()) {
            return;
        }

        $customerId = $shipment->getOrder()->getCustomerId();
        if (empty($customerId) || $shipment->getOrder()->getCustomerIsGuest()) {
            return;
        }

        $customer = $this->customerFactory->create()->load($customerId);

        $shipment->getOrder()->setData(
            'customer_' . \M2E\TikTokShop\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(\M2E\TikTokShop\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE)
        );
    }

    //########################################
}
