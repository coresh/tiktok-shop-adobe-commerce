<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order;

class StatusHelper
{
    public function getStatusesOptions(): array
    {
        return [
            \M2E\TikTokShop\Model\Order::STATUS_PENDING => __('Pending'),
            \M2E\TikTokShop\Model\Order::STATUS_UNSHIPPED => __('Unshipped'),
            \M2E\TikTokShop\Model\Order::STATUS_SHIPPED => __('Shipped'),
            \M2E\TikTokShop\Model\Order::STATUS_CANCELED => __('Canceled'),
        ];
    }

    public function getStatusLabel(int $status): string
    {
        $options = $this->getStatusesOptions();

        return (string)($options[$status] ?? __('Unknown'));
    }

    public function getStatusColor(int $status): string
    {
        switch ($status) {
            case \M2E\TikTokShop\Model\Order::STATUS_PENDING:
                $color = '#808080'; // gray
                break;
            case \M2E\TikTokShop\Model\Order::STATUS_UNSHIPPED:
                $color = '#000000'; // black
                break;
            case \M2E\TikTokShop\Model\Order::STATUS_SHIPPED:
                $color = '#008000'; // green
                break;
            case \M2E\TikTokShop\Model\Order::STATUS_CANCELED:
                $color = '#ff0000'; // red
                break;
            default:
                $color = '#000000'; // black
        }

        return $color;
    }
}
