<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\SellingFormat;

class Diff extends \M2E\TikTokShop\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isQtyDifferent()
            || $this->isPriceDifferent()
            || $this->isOtherDifferent();
    }

    public function isQtyDifferent(): bool
    {
        $keys = [
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MODE,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_CUSTOM_VALUE,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_CUSTOM_ATTRIBUTE,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_PERCENTAGE,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MODIFICATION_MODE,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MIN_POSTED_VALUE,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_QTY_MAX_POSTED_VALUE,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isPriceDifferent(): bool
    {
        $keys = [
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_MODE,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_MODIFIER,
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_FIXED_PRICE_CUSTOM_ATTRIBUTE,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isOtherDifferent(): bool
    {
        $keys = [
            \M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat::COLUMN_CASH_ON_DELIVERY,
        ];

        return $this->isSettingsDifferent($keys);
    }
}
