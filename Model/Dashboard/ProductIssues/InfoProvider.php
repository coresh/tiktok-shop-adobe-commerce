<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Dashboard\ProductIssues;

class InfoProvider implements \M2E\Core\Model\Dashboard\ProductIssues\InfoProviderInterface
{
    private \M2E\Core\Helper\Url $urlHelper;
    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Core\Helper\Url $urlHelper,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\TikTokShop\Model\Product\Repository $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->urlBuilder = $urlBuilder;
        $this->urlHelper = $urlHelper;
    }

    public function hasListedProducts(): bool
    {
        return $this->productRepository->hasListedProducts();
    }

    public function getStartListingUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'm2e_tiktokshop/tiktokshop_listing_create/index',
            ['step' => 1]
        );
    }

    public function getItemsByIssueViewUrl(): string
    {
        return $this->urlBuilder->getUrl('m2e_tiktokshop/product_grid/issues');
    }

    public function getAllItemsViewUrl(int $tagId): string
    {
        return $this->urlHelper->getUrlWithFilter(
            'm2e_tiktokshop/product_grid/allitems',
            ['errors_filter' => $tagId]
        );
    }
}
