<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Category\Get;

class Response
{
    /** @var \M2E\TikTokShop\Model\TikTokShop\Connector\Category\Category[] */
    private array $categories = [];

    public function addCategory(
        \M2E\TikTokShop\Model\TikTokShop\Connector\Category\Category $category
    ): void {
        $this->categories[] = $category;
    }

    /**
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Category\Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
}
