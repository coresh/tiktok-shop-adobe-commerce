<?php

namespace M2E\TikTokShop\Model\Category\Dictionary\Attribute;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class BrandAttribute extends \M2E\TikTokShop\Model\Category\Dictionary\AbstractAttribute
{
    public function getType(): string
    {
        return CategoryAttribute::ATTRIBUTE_TYPE_BRAND;
    }
}
