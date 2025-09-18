<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Attribute;

use M2E\TikTokShop\Model\Category\CategoryAttribute;
use M2E\TikTokShop\Model\ResourceModel\Category\Attribute as AttributeResource;

class Repository
{
    private \M2E\TikTokShop\Model\ResourceModel\Category\Attribute\CollectionFactory $attributeCollectionFactory;
    private AttributeResource $attributeResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Category\Attribute\CollectionFactory $attributeCollectionFactory,
        AttributeResource $attributeResource
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeResource = $attributeResource;
    }

    public function create(CategoryAttribute $entity): void
    {
        $this->attributeResource->save($entity);
    }

    public function save(CategoryAttribute $attrEntity): void
    {
        $this->attributeResource->save($attrEntity);
    }

    public function delete(CategoryAttribute $attrEntity): void
    {
        $this->attributeResource->delete($attrEntity);
    }

    /**
     * @return CategoryAttribute[]
     */
    public function findByDictionaryId(
        int $dictionaryId,
        array $typeFilter = []
    ): array {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            ['eq' => $dictionaryId]
        );

        if ($typeFilter !== []) {
            $collection->addFieldToFilter(
                AttributeResource::COLUMN_ATTRIBUTE_TYPE,
                ['in' => $typeFilter]
            );
        }

        return array_values($collection->getItems());
    }

    public function getCountByDictionaryId(int $dictionaryId): int
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            $dictionaryId
        );

        return $collection->getSize();
    }

    /**
     * @return string[]
     */
    public function getAllCustomAttributeIds(): array
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_VALUE_MODE,
            \M2E\TikTokShop\Model\Category\CategoryAttribute::VALUE_MODE_CUSTOM_ATTRIBUTE
        );

        $collection->removeAllFieldsFromSelect();

        $collection->addFieldToSelect(AttributeResource::COLUMN_ATTRIBUTE_ID);
        $collection->distinct(true);

        $result = [];
        /** @var \M2E\TikTokShop\Model\Category\CategoryAttribute $item */
        foreach ($collection->getItems() as $item) {
            $result[] = $item->getAttributeId();
        }

        return $result;
    }

    /**
     * @param int $dictionaryId
     *
     * @return CategoryAttribute[]
     */
    public function getAttributesWithCustomValue(int $dictionaryId): array
    {
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter(
            AttributeResource::COLUMN_CATEGORY_DICTIONARY_ID,
            ['eq' => $dictionaryId]
        );

        $collection->addFieldToFilter(
            AttributeResource::COLUMN_VALUE_MODE,
            CategoryAttribute::VALUE_MODE_CUSTOM_ATTRIBUTE
        );

        $collection->addFieldToFilter(
            AttributeResource::COLUMN_ATTRIBUTE_TYPE,
            [
                'in' => [
                    CategoryAttribute::ATTRIBUTE_TYPE_PRODUCT,
                    CategoryAttribute::ATTRIBUTE_TYPE_BRAND,
                    CategoryAttribute::ATTRIBUTE_TYPE_CERTIFICATE,
                    CategoryAttribute::ATTRIBUTE_TYPE_SIZE_CHART,
                ],
            ]
        );

        return array_values($collection->getItems());
    }
}
