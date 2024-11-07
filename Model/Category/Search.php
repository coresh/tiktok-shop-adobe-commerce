<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category;

use M2E\TikTokShop\Model\Category\Search\ResultCollection;
use M2E\TikTokShop\Model\Category\Search\ResultItem;

class Search
{
    private \M2E\TikTokShop\Model\Category\Tree\Repository $categoryRepository;
    private \M2E\TikTokShop\Model\Category\Tree\PathBuilder $pathBuilder;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Tree\Repository $categoryRepository,
        \M2E\TikTokShop\Model\Category\Tree\PathBuilder $pathBuilder
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->pathBuilder = $pathBuilder;
    }

    public function process(int $shopId, string $searchQuery, int $limit): ResultCollection
    {
        $resultCollection = new ResultCollection($limit);
        $foundedItems = $this->categoryRepository->searchByTitleOrId($shopId, $searchQuery, $limit);
        if (count($foundedItems) === 0) {
            return $resultCollection;
        }

        foreach ($foundedItems as $item) {
            if ($item->isLeaf()) {
                $this->addLeafItem($resultCollection, $item);

                continue;
            }

            $this->addCategoryChildren($resultCollection, $item);
            if ($resultCollection->getCount() > $limit) {
                break;
            }
        }

        return $resultCollection;
    }

    private function addLeafItem(ResultCollection $resultCollection, Tree $treeItem): void
    {
        $resultCollection->add(
            new ResultItem(
                $treeItem->getCategoryId(),
                $this->pathBuilder->getPath($treeItem),
                $treeItem->isInviteOnly()
            )
        );
    }

    private function addCategoryChildren(ResultCollection $resultCollection, Tree $treeItem): void
    {
        $children = $this->categoryRepository
            ->getChildren($treeItem->getShopId(), (int)$treeItem->getCategoryId());

        foreach ($children as $child) {
            $this->addLeafItem($resultCollection, $child);
        }
    }
}
