<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync;

use M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality;
use M2E\TikTokShop\Model\Listing\InventorySync\Channel\ListingQuality\Recommendation;

class ProductBuilder
{
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\Shop $shop;

    public function __construct(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Shop $shop
    ) {
        $this->account = $account;
        $this->shop = $shop;
    }

    public function build(array $response): \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductCollection
    {
        $result = new \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductCollection();
        foreach ($response as $unmanagedItem) {
            $productId = $unmanagedItem['id'];
            $title = $unmanagedItem['title'];

            $variants = $unmanagedItem['skus'];
            $ttsProductVariantCollection = new \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSkuCollection();
            foreach ($variants as $variant) {
                $ttsVariant = new \M2E\TikTokShop\Model\Listing\InventorySync\Channel\ProductSku(
                    $variant['id'],
                    $variant['seller_sku'],
                    $variant['price']['currency'],
                    (float)$variant['price']['sale_price'],
                    $this->getQty($variant['inventory']),
                    $this->getTtsWarehouseId($variant['inventory']),
                    $this->findIdentifier($variant),
                    $variant['sales_attributes']
                );

                $ttsProductVariantCollection->add($ttsVariant);
            }

            $listingQuality = new ListingQuality($unmanagedItem['listing_quality_tier']);
            foreach ($unmanagedItem['listing_quality_recommendations'] ?? [] as $rawRecommendation) {
                $recommendation = new Recommendation(
                    $rawRecommendation['code'],
                    $rawRecommendation['field'],
                    $rawRecommendation['section'],
                    $rawRecommendation['how_to_solve'],
                    $rawRecommendation['quality_tier']
                );
                $listingQuality->addRecommendation($recommendation);
            }

            $ttsProduct = new \M2E\TikTokShop\Model\Listing\InventorySync\Channel\Product(
                $this->account->getId(),
                $this->shop->getId(),
                $productId,
                \M2E\TikTokShop\Model\Listing\InventorySync\Channel\Product::convertChannelStatusToExtension(
                    $unmanagedItem['status'],
                ),
                $title,
                $this->resolveCategoryId($unmanagedItem['category_chains']),
                $unmanagedItem['category_chains'],
                $unmanagedItem['manufacturer_ids'],
                $unmanagedItem['responsible_person_ids'],
                $ttsProductVariantCollection,
                $listingQuality,
                $unmanagedItem['is_not_for_sale']
            );

            $result->add($ttsProduct);
        }

        return $result;
    }

    private function getQty(array $inventory): int
    {
        $firstWarehouse = reset($inventory);

        return (int)$firstWarehouse['quantity'];
    }

    private function getTtsWarehouseId(array $inventory): string
    {
        $firstWarehouse = reset($inventory);

        return $firstWarehouse['warehouse_id'];
    }

    private function resolveCategoryId(array $categoryData): ?string
    {
        foreach ($categoryData as $category) {
            if ($category['is_leaf']) {
                return  $category['id'];
            }
        }

        return null;
    }

    private function findIdentifier(array $variantSku): ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier
    {
        $identifierData = $variantSku['identifier'] ?? [];
        if (empty($identifierData)) {
            return null;
        }

        $code = $identifierData['code'] ?? null;
        $type = $identifierData['type'] ?? null;

        if (empty($code) || empty($type)) {
            return null;
        }

        $availableIdentifierTypes = [
            \M2E\TikTokShop\Helper\Data\Product\Identifier::EAN,
            \M2E\TikTokShop\Helper\Data\Product\Identifier::GTIN,
            \M2E\TikTokShop\Helper\Data\Product\Identifier::ISBN,
            \M2E\TikTokShop\Helper\Data\Product\Identifier::UPC,
        ];

        if (!in_array($type, $availableIdentifierTypes)) {
            return null;
        }

        return new \M2E\TikTokShop\Model\Product\VariantSku\Identifier($code, $type);
    }
}
