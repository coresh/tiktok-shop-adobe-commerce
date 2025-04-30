<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ListAction;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;
use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\VariantSku\Collection as VariantSkuCollection;

class Response extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(\M2E\TikTokShop\Model\Product\Repository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        /** @see Request::getActionData() */
        $requestMetadata = $this->getRequestMetaData();

        $product = $this->getListingProduct();

        $product->setStatusListed($response['product_id'], $this->getStatusChanger());

        if (array_key_exists('online_brand_name', $requestMetadata[DataBuilder\Brand::NICK])) {
            $product->setOnlineBrandName($requestMetadata[DataBuilder\Brand::NICK]['online_brand_name']);
        }

        if (array_key_exists(DataBuilder\NonSalableFlag::NICK, $requestMetadata)) {
            $product->setIsGift($requestMetadata[DataBuilder\NonSalableFlag::NICK]);
        }

        $this->processVariants($response['skus']);

        $product->setOnlineBrandId($response['brand_id'] ?? null)
                ->setOnlineDescription($requestMetadata[DataBuilder\Description::NICK]['online_description'])
                ->setOnlineTitle($requestMetadata[DataBuilder\Title::NICK]['online_title'])
                ->setOnlineMainCategory($requestMetadata[DataBuilder\Categories::NICK]['online_category_id'])
                ->setOnlineCategoryData($requestMetadata[DataBuilder\Categories::NICK]['online_category_data'])
                ->setOnlineManufacturerId($requestMetadata[DataBuilder\Compliance::NICK]['online_manufacturer_id'])
                ->setOnlineResponsiblePersonIds(
                    $requestMetadata[DataBuilder\Compliance::NICK]['online_responsible_person_ids']
                )
                ->removeBlockingByError()
                ->recalculateOnlineDataByVariants();

        $this->productRepository->save($product);
    }

    private function processVariants(array $responseSkus): void
    {
        $responseVariantSku = [];
        foreach ($responseSkus as $sku) {
            $responseVariantSku[$sku['seller_sku']] = [
                'sku_id' => $sku['id'],
                'seller_sku' => $sku['seller_sku'],
            ];
        }

        foreach ($this->getListingProduct()->getVariants() as $variant) {
            if (
                !$this->getVariantSettings()->hasVariantId($variant->getId())
                || $this->getVariantSettings()->isSkipAction($variant->getId())
            ) {
                continue;
            }

            $variantSkuSellerSku = $variant->getSku();

            if (!isset($responseVariantSku[$variantSkuSellerSku])) {
                continue;
            }

            $variant
                ->setSkuId($responseVariantSku[$variantSkuSellerSku]['sku_id'] ?? '')
                ->setOnlineSku($this->getOnlineSkuForVariant($variantSkuSellerSku))
                ->setOnlineQty($this->getOnlineQtyForVariant($variantSkuSellerSku))
                ->setOnlineCurrentPrice($this->getOnlinePriceForVariant($variantSkuSellerSku))
                ->setOnlineImage($this->getOnlineImageForVariant($variantSkuSellerSku))
                ->setOnlineIdentifier($this->getOnlineIdentifierForVariant($variantSkuSellerSku))
                ->changeStatusToListed();

            $this->productRepository->saveVariantSku($variant);
        }
    }

    // ----------------------------------------

    private function getOnlineQtyFromVariants(): int
    {
        $metadata = $this->getRequestMetaData()[DataBuilder\VariantSku::NICK];
        if (empty($metadata)) {
            return 0;
        }

        $qty = 0;
        foreach ($metadata as $variantData) {
            $qty += $variantData['online_qty'] ?? 0;
        }

        return $qty;
    }

    // ----------------------------------------

    private function getWarehouseForVariant(string $sku): int
    {
        $metadata = $this->getRequestMetaData()[DataBuilder\VariantSku::NICK];

        return $metadata[$sku]['warehouse_id'];
    }

    private function getOnlineSkuForVariant(string $sku): string
    {
        $metadata = $this->getRequestMetaData()[DataBuilder\VariantSku::NICK];

        return $metadata[$sku]['online_sku'] ?? '';
    }

    private function getOnlinePriceForVariant(string $sku): float
    {
        $metadata = $this->getRequestMetaData()[DataBuilder\VariantSku::NICK];

        return $metadata[$sku]['online_price'] ?? 0;
    }

    private function getOnlineQtyForVariant(string $sku): int
    {
        $metadata = $this->getRequestMetaData()[DataBuilder\VariantSku::NICK];

        return $metadata[$sku]['online_qty'] ?? 0;
    }

    private function getOnlineImageForVariant(string $sku): string
    {
        $metadata = $this->getRequestMetaData()[DataBuilder\VariantSku::NICK];

        return $metadata[$sku]['online_image'] ?? '';
    }

    private function getOnlineIdentifierForVariant(string $sku): ?\M2E\TikTokShop\Model\Product\VariantSku\Identifier
    {
        $metadata = $this->getRequestMetaData()[DataBuilder\VariantSku::NICK];
        if (empty($metadata)) {
            return null;
        }

        $identifierData = $metadata[$sku][VariantSkuCollection::METADATA_KEY_ONLINE_IDENTIFIER] ?? [];

        if (empty($identifierData)) {
            return null;
        }

        return new \M2E\TikTokShop\Model\Product\VariantSku\Identifier(
            $identifierData[VariantSkuCollection::METADATA_KEY_ONLINE_IDENTIFIER_ID],
            $identifierData[VariantSkuCollection::METADATA_KEY_ONLINE_IDENTIFIER_TYPE]
        );
    }
}
