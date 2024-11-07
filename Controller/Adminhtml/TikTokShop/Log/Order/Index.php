<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Log\Order;

class Index extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Log\AbstractOrder
{
    private \M2E\TikTokShop\Model\OrderFactory $orderFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Order $orderResource;

    public function __construct(
        \M2E\TikTokShop\Model\OrderFactory $orderFactory,
        \M2E\TikTokShop\Model\ResourceModel\Order $orderResource
    ) {
        parent::__construct();
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id', false);

        if ($orderId) {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, (int)$orderId);

            if ($order->isObjectNew()) {
                $this->getMessageManager()->addError(__('Listing does not exist.')); // Listing ???

                return $this->_redirect('*/*/index');
            }

            $this->setPageTitle(
                __('Order #%order_id Log', ['order_id' => $order->getTtsOrderId()])
            );
        } else {
            $this->setPageTitle(__('Orders Logs & Events'));
        }

        $this->addContent($this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Log\Order::class));

        return $this->getResult();
    }

    private function setPageTitle(string $pageTitle): void
    {
        $this->getResult()->getConfig()->getTitle()->prepend($pageTitle);
    }
}
