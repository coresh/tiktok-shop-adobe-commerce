<?php

namespace M2E\TikTokShop\Plugin\Order\Magento;

class View extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    private \M2E\TikTokShop\Model\ResourceModel\Order $orderResource;
    private \M2E\TikTokShop\Model\OrderFactory $orderFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Order $orderResource,
        \M2E\TikTokShop\Model\OrderFactory $orderFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct($helperFactory);
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function aroundSetLayout(
        \Magento\Framework\View\Element\AbstractBlock $interceptor,
        \Closure $callback,
        ...$arguments
    ) {
        if (!($interceptor instanceof \Magento\Sales\Block\Adminhtml\Order\View)) {
            return $callback(...$arguments);
        }

        return $this->execute('setLayout', $interceptor, $callback, $arguments);
    }

    protected function processSetLayout($interceptor, \Closure $callback, array $arguments)
    {
        /** @var \Magento\Sales\Block\Adminhtml\Order\View $interceptor */
        $magentoOrderId = $interceptor->getRequest()->getParam('order_id');
        if (empty($magentoOrderId)) {
            return $callback(...$arguments);
        }

        $order = $this->findOrder((int)$magentoOrderId);
        if ($order === null) {
            return $callback(...$arguments);
        }

        $buttonUrl = $interceptor->getUrl(
            'm2e_tiktokshop/tiktokshop_order/view',
            ['id' => $order->getId()]
        );

        $interceptor->addButton(
            'go_to_tiktokshop_order',
            [
                'label' => __('Show TikTok Shop Order'),
                'onclick' => "setLocation('$buttonUrl')",
            ],
            0,
            -1
        );

        return $callback(...$arguments);
    }

    //########################################

    private function findOrder(int $magentoOrderId): ?\M2E\TikTokShop\Model\Order
    {
        try {
            $order = $this->orderFactory->create();
            $this->orderResource->loadByMagentoOrderId($order, $magentoOrderId);

            if ($order->isObjectNew()) {
                return null;
            }
        } catch (\Throwable $exception) {
            return null;
        }

        return $order;
    }

    //########################################
}
