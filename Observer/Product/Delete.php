<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Observer\Product;

class Delete extends AbstractProduct
{
    private \M2E\TikTokShop\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct;
    private \M2E\TikTokShop\Model\Listing\Other\UnmapDeletedProduct $unmanagedUnmapDeletedProduct;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Other\UnmapDeletedProduct $unmanagedUnmapDeletedProduct,
        \M2E\TikTokShop\Model\Listing\RemoveDeletedProduct $listingRemoveDeletedProduct,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct(
            $productFactory,
            $ourMagentoProductFactory,
            $helperFactory
        );
        $this->unmanagedUnmapDeletedProduct = $unmanagedUnmapDeletedProduct;
        $this->listingRemoveDeletedProduct = $listingRemoveDeletedProduct;
    }

    public function process(): void
    {
        if (empty($this->getProductId())) {
            return;
        }

        $this->unmanagedUnmapDeletedProduct->process($this->getProduct());
        $this->listingRemoveDeletedProduct->process($this->getProduct());
    }
}
