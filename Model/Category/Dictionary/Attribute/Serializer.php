<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary\Attribute;

class Serializer
{
    private \M2E\TikTokShop\Model\Category\Dictionary\AttributeFactory $attributeFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\AttributeFactory $attributeFactory
    ) {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @param SalesAttribute[] $attributes
     */
    public function serializeSalesAttributes(array $attributes): string
    {
        $data = [];
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof SalesAttribute) {
                throw new \LogicException('Invalid attribute instance');
            }

            $data[] = [
                'id' => $attribute->getId(),
                'name' => $attribute->getName(),
                'is_required' => $attribute->isRequired(),
                'is_customised' => $attribute->isCustomised(),
                'is_multiple_selected' => $attribute->isMultipleSelected(),
            ];
        }

        return json_encode($data);
    }

    /**
     * @return SalesAttribute[]
     */
    public function unSerializeSalesAttributes(string $jsonAttributes): array
    {
        $attributes = [];
        foreach (json_decode($jsonAttributes, true) as $item) {
            $attributes[] = $this->attributeFactory->createSalesAttribute(
                $item['id'],
                $item['name'],
                $item['is_required'],
                $item['is_customised'],
                $item['is_multiple_selected'],
            );
        }

        return $attributes;
    }

    /**
     * @param ProductAttribute[] $attributes
     *
     * @return string
     */
    public function serializeProductAttributes(array $attributes): string
    {
        $data = [];
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof ProductAttribute) {
                throw new \LogicException('Invalid attribute instance');
            }

            $values = [];
            foreach ($attribute->getValues() as $value) {
                $values[] = [
                    'id' => $value->getId(),
                    'name' => $value->getName(),
                ];
            }

            $data[] = [
                'id' => $attribute->getId(),
                'name' => $attribute->getName(),
                'is_required' => $attribute->isRequired(),
                'is_customised' => $attribute->isCustomised(),
                'is_multiple_selected' => $attribute->isMultipleSelected(),
                'values' => $values,
            ];
        }

        return json_encode($data);
    }

    /**
     * @return SalesAttribute[]
     */
    public function unSerializeProductAttributes(string $jsonAttributes): array
    {
        $attributes = [];
        foreach (json_decode($jsonAttributes, true) as $item) {
            $values = [];
            foreach ($item['values'] as $value) {
                $values[] = $this->attributeFactory->createValue(
                    $value['id'],
                    $value['name']
                );
            }

            $attributes[] = $this->attributeFactory->createProductAttribute(
                $item['id'],
                $item['name'],
                $item['is_required'],
                $item['is_customised'],
                $item['is_multiple_selected'],
                $values
            );
        }

        return $attributes;
    }
}
