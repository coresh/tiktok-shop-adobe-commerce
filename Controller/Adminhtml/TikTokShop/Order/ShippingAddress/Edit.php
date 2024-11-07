<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Order\ShippingAddress;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder;

class Edit extends AbstractOrder
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
        $orderId = $this->getRequest()->getParam('id');
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId);

        $form = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\Edit\ShippingAddress\Form::class, '', [
                'order' => $order,
            ]);

        $this->setAjaxContent($form->toHtml());

        return $this->getResult();
    }
}
