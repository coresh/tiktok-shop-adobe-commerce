<?php

namespace M2E\TikTokShop\Model\Category\Dictionary\Attribute;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class CertificateAttribute extends \M2E\TikTokShop\Model\Category\Dictionary\AbstractAttribute
{
    public function getType(): string
    {
        return \M2E\TikTokShop\Model\Category\CategoryAttribute::ATTRIBUTE_TYPE_CERTIFICATE;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
