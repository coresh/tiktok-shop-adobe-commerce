<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\AttributeMapping\General;

class Pair
{
    public string $type;
    public string $channelAttributeTitle;
    public string $channelAttributeCode;
    public ?string $magentoAttributeCode;

    public function __construct(
        string $type,
        string $channelAttributeTitle,
        string $channelAttributeCode,
        ?string $magentoAttributeCode
    ) {
        $this->type = $type;
        $this->channelAttributeTitle = $channelAttributeTitle;
        $this->channelAttributeCode = $channelAttributeCode;
        $this->magentoAttributeCode = $magentoAttributeCode;
    }
}
