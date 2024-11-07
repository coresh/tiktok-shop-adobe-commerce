<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order\View;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class CancellationRequest extends AbstractContainer
{
    protected $_template = 'tiktokshop/order/cancellation.phtml';

    private \M2E\TikTokShop\Model\Order $order;

    public function __construct(
        \M2E\TikTokShop\Model\Order $order,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->order = $order;
        parent::__construct($context, $data);
    }

    public function getBuyerCancellationRequestReason(): string
    {
        return $this->order->getBuyerCancellationRequestReason();
    }

    /**
     * @return array{label: string, value:string}
     */
    public function getDeclineReasons(): array
    {
        return [
            [
                'label' => __('Invalid Reason for Cancellation'),
                'value' => \M2E\TikTokShop\Model\Order\CancellationRequest\Decline::REASON_INVALID_REASON,
            ],
            [
                'label' => __('Product Delivery is on Schedule'),
                'value' => \M2E\TikTokShop\Model\Order\CancellationRequest\Decline::REASON_DELIVERY_SCHEDULE,
            ],
            [
                'label' => __('Reached an Agreement With the Buyer'),
                'value' => \M2E\TikTokShop\Model\Order\CancellationRequest\Decline::REASON_REACHED_AGREEMENT,
            ],
            [
                'label' => __('Product Has Been Packed'),
                'value' => \M2E\TikTokShop\Model\Order\CancellationRequest\Decline::REASON_PRODUCT_PACKED,
            ],
        ];
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->add(
            $this->getUrl('*/order_cancellation/accept', ['order_id' => $this->order->getId()]),
            'order_cancellation/accept'
        );

        $this->jsUrl->add(
            $this->getUrl('*/order_cancellation/decline', ['order_id' => $this->order->getId()]),
            'order_cancellation/decline'
        );

        $declineReasons = $this->_escaper->escapeJs(
            json_encode($this->getDeclineReasons(), JSON_THROW_ON_ERROR)
        );

        $js = <<<JS
require([
    'TikTokShop/Order/OrderCancellation',
], function () {
    window.OrderCancellationObj = new window.OrderCancellation(
        '.buyer-cancellation-accept',
        '.buyer-cancellation-decline',
        '$declineReasons'
    );
});
JS;

        $this->js->add($js);

        return parent::_beforeToHtml();
    }
}
