<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Search;

class ResultItem
{
    public string $categoryId;
    public string $path;
    public bool $isInviteOnly;
    public bool $isValid;

    public function __construct(
        string $categoryId,
        string $path,
        bool $isInviteOnly,
        bool $isValid
    ) {
        $this->categoryId = $categoryId;
        $this->path = $path;
        $this->isInviteOnly = $isInviteOnly;
        $this->isValid = $isValid;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->categoryId,
            'path' => $this->path,
            'is_invite' => $this->isInviteOnly,
            'is_valid' => $this->isValid
        ];
    }
}
