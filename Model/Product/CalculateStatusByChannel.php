<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Product;

use M2E\TikTokShop\Model\Listing\InventorySync\Channel\Product as ChannelProduct;
use M2E\TikTokShop\Model\Product as ExtensionProduct;

class CalculateStatusByChannel
{
    /**
     * @param \M2E\TikTokShop\Model\Product $product
     * @param string|int $channelStatus
     *
     * @return \M2E\TikTokShop\Model\Product\CalculateStatusByChannel\Result|null
     */
    public function calculate(ExtensionProduct $product, $channelStatus): ?CalculateStatusByChannel\Result
    {
        if (is_string($channelStatus)) {
            $extensionStatusFromChannel = ChannelProduct::convertChannelStatusToExtension(
                $channelStatus,
            );
        } else {
            $extensionStatusFromChannel = (int)$channelStatus;
        }

        if ($this->isStatusRight($product, $extensionStatusFromChannel)) {
            return null;
        }

        if (
            $extensionStatusFromChannel === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED // Deleted case
            && $product->getStatus() !== \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED
        ) {
            $calculateStatus = \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED;
            $actionMessage = new \M2E\TikTokShop\Model\Listing\Log\Record(
                (string)__('Product was deleted and is no longer available on the channel'),
                \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_SUCCESS,
            );
        } elseif ($extensionStatusFromChannel === \M2E\TikTokShop\Model\Product::STATUS_BLOCKED) {
            $calculateStatus = \M2E\TikTokShop\Model\Product::STATUS_BLOCKED;
            $actionMessage = new \M2E\TikTokShop\Model\Listing\Log\Record(
                (string)__(
                    'Product status was changed on TikTok Shop. Please check the reasons and suggestions for improving the product information on the channel.',
                ),
                \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
            );
        } else {
            $calculateStatus = $extensionStatusFromChannel;
            $actionMessage = new \M2E\TikTokShop\Model\Listing\Log\Record(
                (string)__(
                    'Item Status was changed from "%from" to "%to".',
                    [
                        'from' => \M2E\TikTokShop\Model\Product::getStatusTitle($product->getStatus()),
                        'to' => \M2E\TikTokShop\Model\Product::getStatusTitle($extensionStatusFromChannel),
                    ],
                ),
                \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_SUCCESS,
            );
        }

        return new CalculateStatusByChannel\Result(
            $product,
            $calculateStatus,
            $actionMessage,
        );
    }

    private function isStatusRight(ExtensionProduct $product, int $channelStatus): bool
    {
        return $product->getStatus() === $channelStatus;
    }
}
