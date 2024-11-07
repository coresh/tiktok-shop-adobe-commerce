<?php

namespace M2E\TikTokShop\Observer\Order;

class View extends \M2E\TikTokShop\Observer\AbstractObserver
{
    protected \Magento\Customer\Model\CustomerFactory $customerFactory;
    protected \Magento\Framework\Registry $registry;
    private \M2E\TikTokShop\Model\OrderFactory $orderFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order $orderResource;
    private \Magento\Customer\Model\ResourceModel\Customer $customerResource;

    public function __construct(
        \M2E\TikTokShop\Model\OrderFactory $orderFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order $orderResource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Magento\Framework\Registry $registry,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct($helperFactory);
        $this->customerFactory = $customerFactory;
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
        $this->customerResource = $customerResource;
    }

    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order $magentoOrder */
        $magentoOrder = $this->registry->registry('current_order');
        if (empty($magentoOrder) || !$magentoOrder->getId()) {
            return;
        }

        try {
            $order = $this->orderFactory->create();
            $this->orderResource->loadByMagentoOrderId($order, $magentoOrder->getId());
        } catch (\Throwable $exception) {
            return;
        }

        if ($order->isObjectNew()) {
            return;
        }

        $customerId = $magentoOrder->getCustomerId();
        if (empty($customerId) || $magentoOrder->getCustomerIsGuest()) {
            return;
        }

        $customer = $this->customerFactory->create();
        $this->customerResource->load($customer, $customerId);

        $magentoOrder->setData(
            'customer_' . \M2E\TikTokShop\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(\M2E\TikTokShop\Model\Order\ProxyObject::USER_ID_ATTRIBUTE_CODE)
        );
    }
}
