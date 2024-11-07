<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Custom;

use M2E\TikTokShop\Model\Magento\Product\Rule\Condition\AbstractModel;

abstract class AbstractCustomFilter
{
    abstract public function getLabel(): string;

    /**
     * @return mixed
     */
    abstract public function getValueByProductInstance(\Magento\Catalog\Model\Product $product);

    public function getInputType(): string
    {
        return AbstractModel::INPUT_TYPE_STRING;
    }

    public function getValueElementType(): string
    {
        return AbstractModel::VALUE_ELEMENT_TYPE_TEXT;
    }

    public function getOptions(): array
    {
        return [];
    }
}
