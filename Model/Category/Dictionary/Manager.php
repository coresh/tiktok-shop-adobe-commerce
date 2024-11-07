<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Category\Dictionary;

use M2E\TikTokShop\Model\Category\Dictionary;

class Manager
{
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \M2E\TikTokShop\Model\Category\Dictionary\CreateService $createService;

    public function __construct(
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Category\Dictionary\CreateService $createService
    ) {
        $this->dictionaryRepository = $dictionaryRepository;
        $this->shopRepository = $shopRepository;
        $this->createService = $createService;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getOrCreateDictionary(int $shopId, string $categoryId): Dictionary
    {
        $entity = $this->dictionaryRepository->findByShopAndCategoryId($shopId, $categoryId);
        if ($entity !== null) {
            return $entity;
        }

        $shop = $this->shopRepository->find($shopId);
        if ($shop === null) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                sprintf('Not found shop by id [%d]', $shopId)
            );
        }

        return $this->createService->create($shop, $categoryId);
    }
}
