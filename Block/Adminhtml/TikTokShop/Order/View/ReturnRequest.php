<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\View;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class ReturnRequest extends AbstractContainer
{
    protected $_template = 'tiktokshop/order/return_request.phtml';

    private \M2E\TikTokShop\Model\Order $order;

    public function __construct(
        \M2E\TikTokShop\Model\Order $order,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->order = $order;
        parent::__construct($context, $data);
    }

    public function getOrderId(): ?int
    {
        return $this->order->getId();
    }
}
