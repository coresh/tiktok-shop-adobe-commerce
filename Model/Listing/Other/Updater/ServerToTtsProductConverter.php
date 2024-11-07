<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other\Updater;

class ServerToTtsProductConverter
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

    public function convert(array $response): \M2E\TikTokShop\Model\Listing\Other\TtsProductCollection
    {
        $result = new \M2E\TikTokShop\Model\Listing\Other\TtsProductCollection();
        foreach ($response as $unmanagedItem) {
            $productId = $unmanagedItem['id'];
            $title = $unmanagedItem['title'];

            $variantSkus = $unmanagedItem['skus'];
            $ttsProductVariantCollection = new \M2E\TikTokShop\Model\Listing\Other\TtsProductSkuCollection();
            foreach ($variantSkus as $variantSku) {
                $ttsVariantSku = new \M2E\TikTokShop\Model\Listing\Other\TtsProductSku(
                    $variantSku['id'],
                    $variantSku['seller_sku'],
                    $variantSku['price']['currency'],
                    (float)$variantSku['price']['sale_price'],
                    $this->getQty($variantSku['inventory']),
                    $this->getTtsWarehouseId($variantSku['inventory']),
                    $variantSku['inventory'],
                    $this->findIdentifier($variantSku)
                );

                $ttsProductVariantCollection->add($ttsVariantSku);
            }

            $ttsProduct = new \M2E\TikTokShop\Model\Listing\Other\TtsProduct(
                $this->account->getId(),
                $this->shop->getId(),
                $productId,
                \M2E\TikTokShop\Model\Listing\Other\TtsProduct::convertChannelStatusToExtension(
                    $unmanagedItem['status'],
                ),
                $title,
                $this->resolveCategoryId($unmanagedItem['category_chains']),
                $unmanagedItem['category_chains'],
                $ttsProductVariantCollection
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
