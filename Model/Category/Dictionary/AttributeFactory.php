<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary;

class AttributeFactory
{
    public function createSalesAttribute(
        string $id,
        string $name,
        bool $isRequired,
        bool $isCustomised,
        bool $isMultipleSelected
    ): \M2E\TikTokShop\Model\Category\Dictionary\Attribute\SalesAttribute {
        return new \M2E\TikTokShop\Model\Category\Dictionary\Attribute\SalesAttribute(
            $id,
            $name,
            $isRequired,
            $isCustomised,
            $isMultipleSelected
        );
    }

    /**
     * @param \M2E\TikTokShop\Model\Category\Dictionary\Attribute\Value[] $values
     */
    public function createProductAttribute(
        string $id,
        string $name,
        bool $isRequired,
        bool $isCustomised,
        bool $isMultipleSelected,
        array $values
    ): \M2E\TikTokShop\Model\Category\Dictionary\Attribute\ProductAttribute {
        return new \M2E\TikTokShop\Model\Category\Dictionary\Attribute\ProductAttribute(
            $id,
            $name,
            $isRequired,
            $isCustomised,
            $isMultipleSelected,
            $values
        );
    }

    public function createValue(string $id, string $name): \M2E\TikTokShop\Model\Category\Dictionary\Attribute\Value
    {
        return new \M2E\TikTokShop\Model\Category\Dictionary\Attribute\Value($id, $name);
    }
}
