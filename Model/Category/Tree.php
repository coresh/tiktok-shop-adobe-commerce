<?php

namespace M2E\TikTokShop\Model\Category;

use M2E\TikTokShop\Model\ResourceModel\Category\Tree as CategoryTreeResource;

class Tree extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const PERMISSION_STATUSES_NON_MAIN_CATEGORY = 'NON_MAIN_CATEGORY';
    public const PERMISSION_STATUSES_INVITE_ONLY = 'INVITE_ONLY';
    public const PERMISSION_STATUSES_AVAILABLE = 'AVAILABLE';

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(CategoryTreeResource::class);
    }

    public function create(
        int $shopEntityId,
        string $categoryId,
        ?string $parentCategoryId,
        string $title,
        bool $isLeaf,
        array $permissionStatuses
    ): self {
        $this->setData(CategoryTreeResource::COLUMN_SHOP_ID, $shopEntityId);
        $this->setData(CategoryTreeResource::COLUMN_CATEGORY_ID, $categoryId);
        $this->setData(CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID, $parentCategoryId);
        $this->setData(CategoryTreeResource::COLUMN_TITLE, $title);
        $this->setData(CategoryTreeResource::COLUMN_IS_LEAF, $isLeaf);
        $this->setPermissionStatuses($permissionStatuses);

        return $this;
    }

    public function getShopId(): int
    {
        return (int)$this->getData(CategoryTreeResource::COLUMN_SHOP_ID);
    }

    public function getCategoryId(): string
    {
        return $this->getData(CategoryTreeResource::COLUMN_CATEGORY_ID);
    }

    public function getTitle(): string
    {
        return $this->getData(CategoryTreeResource::COLUMN_TITLE);
    }

    public function isLeaf(): bool
    {
        return (bool)$this->getData(CategoryTreeResource::COLUMN_IS_LEAF);
    }

    public function getParentCategoryId(): ?string
    {
        if ($parentCategoryId = $this->getDataByKey(CategoryTreeResource::COLUMN_PARENT_CATEGORY_ID)) {
            return (string)$parentCategoryId;
        }

        return null;
    }

    public function setPermissionStatuses(array $permissionStatuses): void
    {
        $this->setData(
            CategoryTreeResource::COLUMN_PERMISSION_STATUSES,
            json_encode($permissionStatuses, JSON_THROW_ON_ERROR)
        );
    }

    public function getPermissionStatuses(): array
    {
        $permissionStatuses = $this->getData(CategoryTreeResource::COLUMN_PERMISSION_STATUSES);
        if ($permissionStatuses === null) {
            return [];
        }

        return json_decode($permissionStatuses, true);
    }

    public function isInviteOnly(): bool
    {
        foreach ($this->getPermissionStatuses() as $permissionStatus) {
            if ($permissionStatus === self::PERMISSION_STATUSES_INVITE_ONLY) {
                return true;
            }
        }

        return false;
    }
}
