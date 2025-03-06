<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Select;

class YesNoAnyOption implements \Magento\Framework\Data\OptionSourceInterface
{
    public const OPTION_YES = 1;
    public const OPTION_NO = 0;

    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('Any')],
            ['value' => self::OPTION_NO, 'label' => __('No')],
            ['value' => self::OPTION_YES, 'label' => __('Yes')],
        ];
    }
}
