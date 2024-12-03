<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Compliance;

class Diff extends \M2E\TikTokShop\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isComplianceDataDifferent();
    }

    public function isComplianceDataDifferent(): bool
    {
        $keys = [
            \M2E\TikTokShop\Model\ResourceModel\Template\Compliance::COLUMN_MANUFACTURER_ID,
            \M2E\TikTokShop\Model\ResourceModel\Template\Compliance::COLUMN_RESPONSIBLE_PERSON_ID
        ];

        return $this->isSettingsDifferent($keys);
    }
}
