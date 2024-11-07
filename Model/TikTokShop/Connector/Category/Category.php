<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Category;

class Category
{
    public const PERMISSION_STATUSES_AVAILABLE = 'AVAILABLE';
    public const PERMISSION_STATUSES_INVITE_ONLY = 'INVITE_ONLY';
    public const PERMISSION_STATUSES_NON_MAIN_CATEGORY = 'NON_MAIN_CATEGORY';

    private string $id;
    private ?string $parentId;
    private string $name;
    private bool $isLeaf;
    /** @var int[] */
    private array $permissionStatuses;

    /**
     * @param int[] $permissionStatuses
     */
    public function __construct(
        string $id,
        ?string $parentId,
        string $name,
        bool $isLeaf,
        array $permissionStatuses
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->name = $name;
        $this->isLeaf = $isLeaf;
        $this->permissionStatuses = $permissionStatuses;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isLeaf(): bool
    {
        return $this->isLeaf;
    }

    public function getPermissionStatuses(): array
    {
        return $this->permissionStatuses;
    }
}
