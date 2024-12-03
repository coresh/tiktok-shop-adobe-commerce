<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Channel;

class Product
{
    /** @link https://partner.tiktokshop.com/docv2/page/650b23eef1fd3102b93d2326 */
    private const PRODUCT_STATUS_ACTIVATE = 'ACTIVATE';
    private const PRODUCT_STATUS_SELLER_DEACTIVATED = 'SELLER_DEACTIVATED';
    private const PRODUCT_STATUS_PLATFORM_DEACTIVATED = 'PLATFORM_DEACTIVATED';
    private const PRODUCT_STATUS_FREEZE = 'FREEZE';
    private const PRODUCT_STATUS_DELETED = 'DELETED';

    private int $accountId;
    private int $shopId;
    private string $productId;
    private int $status;
    private string $title;
    private array $categoriesData;
    private ?string $categoryId;
    private \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSkuCollection $variantCollection;

    public function __construct(
        int $accountId,
        int $shopId,
        string $productId,
        int $status,
        string $title,
        ?string $categoryId,
        array $categoriesData,
        ProductSkuCollection $variantCollection
    ) {
        $this->accountId = $accountId;
        $this->shopId = $shopId;
        $this->productId = $productId;
        $this->status = $status;
        $this->title = $title;
        $this->categoriesData = $categoriesData;
        $this->categoryId = $categoryId;
        $this->variantCollection = $variantCollection;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategoriesData(): array
    {
        return $this->categoriesData;
    }

    public function getVariantCollection(): ProductSkuCollection
    {
        return $this->variantCollection;
    }

    // ----------------------------------------

    public static function convertChannelStatusToExtension(string $channelStatus): int
    {
        if (
            $channelStatus === self::PRODUCT_STATUS_ACTIVATE
        ) {
            return \M2E\TikTokShop\Model\Product::STATUS_LISTED;
        }

        if (
            $channelStatus === self::PRODUCT_STATUS_SELLER_DEACTIVATED
            || $channelStatus === self::PRODUCT_STATUS_PLATFORM_DEACTIVATED
            || $channelStatus === self::PRODUCT_STATUS_FREEZE
        ) {
            return \M2E\TikTokShop\Model\Product::STATUS_INACTIVE;
        }

        if ($channelStatus === self::PRODUCT_STATUS_DELETED) {
            return \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED;
        }

        return \M2E\TikTokShop\Model\Product::STATUS_BLOCKED;
    }
}
