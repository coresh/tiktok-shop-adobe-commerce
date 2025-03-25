<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

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

        if (isset($requestMetadata[DataBuilder\VariantSku::NICK])) {
            $this->updateVariants($product->getVariants(), $response);
        }

        $this->updateProduct($product, $requestMetadata, $response);
    }

    private function updateProduct(
        \M2E\TikTokShop\Model\Product $product,
        array $requestMetadata,
        array $response
    ): void {
        if (isset($requestMetadata[DataBuilder\Title::NICK]['online_title'])) {
            $product->setOnlineTitle($requestMetadata[DataBuilder\Title::NICK]['online_title']);
        }

        if (
            isset($requestMetadata[DataBuilder\Brand::NICK])
            && array_key_exists('online_brand_name', $requestMetadata[DataBuilder\Brand::NICK])
        ) {
            $product->setOnlineBrandName($requestMetadata[DataBuilder\Brand::NICK]['online_brand_name']);
        }

        $product->setOnlineBrandId($response['brand_id'] ?? null);

        if (isset($requestMetadata[DataBuilder\Description::NICK]['online_description'])) {
            $product->setOnlineDescription($requestMetadata[DataBuilder\Description::NICK]['online_description']);
        }

        if (isset($requestMetadata[DataBuilder\Categories::NICK])) {
            $product->setOnlineMainCategory($requestMetadata[DataBuilder\Categories::NICK]['online_category_id'])
                    ->setOnlineCategoryData($requestMetadata[DataBuilder\Categories::NICK]['online_category_data']);
        }

        if (isset($requestMetadata[DataBuilder\VariantSku::NICK])) {
            $product->setOnlineQty($this->getOnlineQtyFromVariants());
        }

        $onlineManufacturerId = $requestMetadata[DataBuilder\Compliance::NICK]['online_manufacturer_id'] ?? null;
        if (!empty($onlineManufacturerId)) {
            $product->setOnlineManufacturerId($onlineManufacturerId);
        }

        $onlineResponsiblePersonIds = $requestMetadata[DataBuilder\Compliance::NICK]['online_responsible_person_ids'] ?? [];
        if (!empty($onlineResponsiblePersonIds)) {
            $product->setOnlineResponsiblePersonIds($onlineResponsiblePersonIds);
        }

        $product
            ->recalculateOnlineDataByVariants()
            ->removeBlockingByError();

        $this->productRepository->save($product);
    }

    /**
     * @param \M2E\TikTokShop\Model\Product\VariantSku[] $variants
     * @param array $response
     *
     * @return void
     */
    private function updateVariants(array $variants, array $response): void
    {
        $responseVariantSku = [];
        foreach ($response['skus'] as $sku) {
            $responseVariantSku[$sku['seller_sku']] = [
                'sku_id' => $sku['id'],
                'seller_sku' => $sku['seller_sku'],
            ];
        }

        foreach ($variants as $variant) {
            if ($this->getVariantSettings()->isSkipAction($variant->getId())) {
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
                ->setOnlineImage($this->getOnlineImageForVariant($variantSkuSellerSku));

            if ($this->getVariantSettings()->isStopAction($variant->getId())) {
                $variant->changeStatusToInactive();
            } else {
                $variant->changeStatusToListed();
            }

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
}
