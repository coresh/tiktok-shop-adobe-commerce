<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

class Promotion implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('Any')],
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Yes')],
        ];
    }
}
