<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

abstract class AbstractAction extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractMain
{
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository
    ) {
        parent::__construct();

        $this->productRepository = $productRepository;
    }

    protected function isRealtimeProcessFromOldGrid(): bool
    {
        return $this->getRequest()->getParam('is_realtime') === 'true';
    }

    /**
     * @param string $listingsProductsIds
     *
     * @return \M2E\TikTokShop\Model\Product[]
     */
    protected function oldGridLoadProducts(string $listingsProductsIds): array
    {
        return $this->productRepository->findByIds(explode(',', $listingsProductsIds));
    }
}
