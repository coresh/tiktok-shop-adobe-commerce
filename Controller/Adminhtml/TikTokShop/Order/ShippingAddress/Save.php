<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Order\ShippingAddress;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractOrder;

class Save extends AbstractOrder
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
        $post = $this->getRequest()->getPost();

        if (!$post->count()) {
            $this->setJsonContent([
                'success' => false,
            ]);

            return $this->getResult();
        }

        $orderId = $this->getRequest()->getParam('id', false);

        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId);

        $data = [];
        $keys = [
            'buyer_name',
            'buyer_email',
        ];

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('buyer_name', $data['buyer_name']);
        $order->setData('buyer_email', $data['buyer_email']);

        $data = [];
        $keys = [
            'recipient_name',
            'street',
            'city',
            'country_code',
            'state',
            'postal_code',
            'phone',
        ];

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (isset($data['street']) && is_array($data['street'])) {
            $data['street'] = array_filter($data['street']);
        }

        $shippingDetails = $order->getShippingDetails();
        $shippingDetails['address'] = $data;

        $order->setData('shipping_details', \M2E\Core\Helper\Json::encode($shippingDetails));
        $order->save();

        $shippingAddressBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Edit\ShippingAddress::class, '', [
                'order' => $order,
            ]);

        $this->setJsonContent([
            'success' => true,
            'html' => $shippingAddressBlock->toHtml(),
        ]);

        return $this->getResult();
    }
}
