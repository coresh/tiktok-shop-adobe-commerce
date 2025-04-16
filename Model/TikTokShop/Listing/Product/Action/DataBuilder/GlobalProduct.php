<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class GlobalProduct extends AbstractDataBuilder
{
    private \M2E\TikTokShop\Model\Image\Repository $imageRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Factory $dataBuilderFactory;

    public function __construct(
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\TikTokShop\Model\Image\Repository $imageRepository,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Factory $dataBuilderFactory
    ) {
        parent::__construct($magentoAttributeHelper);
        $this->imageRepository = $imageRepository;
        $this->dataBuilderFactory = $dataBuilderFactory;
    }

    public const NICK = 'GlobalProduct';

    private array $metaData = [];

    public function getBuilderData(): array
    {
        $globalProduct = $this->getListingProduct()->getGlobalProduct();

        $productData = $this->getProductData($globalProduct);
        $publishProductData = $this->getPublishProductData($globalProduct);

        $this->collectMetaData($productData, $publishProductData);

        return [
            'product_data' => $this->getProductData($globalProduct),
            'publish_product_data' => $this->getPublishProductData($globalProduct),
        ];
    }

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    // ----------------------------------------

    private function getProductData(\M2E\TikTokShop\Model\GlobalProduct $globalProduct): array
    {
        $productData = [
            'title' => $globalProduct->getTitle(),
            'description' => $globalProduct->getDescription(),
            'category_id' => $globalProduct->getCategoryId(),
            'product_attributes' => $globalProduct->getProductAttributes(),
            'brand_id' => $globalProduct->getBrandId(),
            'main_images' => $globalProduct->getMainImages(),
            'package_weight' => $globalProduct->getPackageWeight(),
            'package_dimensions' => $globalProduct->getPackageDimensions(),
            'source_locale' => $globalProduct->getSourceLocale(),
        ];

        if (!empty($sizeChart = $globalProduct->getSizeChart())) {
            $productData['size_chart'] = $sizeChart;
        }

        if (!empty($certifications = $globalProduct->getCertifications())) {
            $productData['certifications'] = $certifications;
        }

        if (!empty($responsiblePersonIds = $globalProduct->getResponsiblePersonIds())) {
            $productData['responsible_person_ids'] = $responsiblePersonIds;
        }

        if (!empty($manufacturerIds = $globalProduct->getManufacturerIds())) {
            $productData['manufacturer_ids'] = $manufacturerIds;
        }

        foreach ($globalProduct->getGlobalVariants() as $variant) {
            $skuData =  [
                'seller_sku' => $variant->getSellerSku(),
                'price' => $variant->getPrice(),
            ];

            if (!empty($variant->getSalesAttributes())) {
                $skuData['sales_attributes'] = $variant->getSalesAttributes();
            }

            if (!empty($variant->getIdentifierCode())) {
                $skuData['identifier_code'] = $variant->getIdentifierCode();
            }

            $productData['skus'][] = $skuData;
        }

        return $productData;
    }

    // ----------------------------------------

    private function getPublishProductData(\M2E\TikTokShop\Model\GlobalProduct $globalProduct): array
    {
        return [
            'global_id' => $globalProduct->getGlobalId(),
            'skus' => $this->getPublishProductDataSkus(),
            'manufacturer_ids' => $this->getPublishProductDataManufacturerIds(),
            'responsible_person_ids' => $this->getPublishProductDataResponsiblePersonIds(),
        ];
    }

    private function getPublishProductDataSkus(): array
    {
        /** @var DataBuilder\VariantSku $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\VariantSku::NICK);

        $productVariantsBySku = [];
        foreach ($dataBuilder->getBuilderData() as $variant) {
            $productVariantsBySku[$variant['seller_sku']] = $variant;
        }

        $skus = [];
        foreach ($this->getListingProduct()->getGlobalProduct()->getGlobalVariants() as $globalVariant) {
            $skus[] = [
                'global_id' => $globalVariant->getGlobalId(),
                'seller_sku' => $globalVariant->getSellerSku(),
                'price' => $productVariantsBySku[$globalVariant->getSellerSku()]['price'],
                'inventory' => reset($productVariantsBySku[$globalVariant->getSellerSku()]['inventory']),
            ];
        }

        return $skus;
    }

    private function getPublishProductDataManufacturerIds(): array
    {
        /** @var DataBuilder\Compliance $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\Compliance::NICK);

        $builderData = $dataBuilder->getBuilderData();

        if (empty($builderData['manufacturer_id'])) {
            return [];
        }

        return [$builderData['manufacturer_id']];
    }

    private function getPublishProductDataResponsiblePersonIds(): array
    {
        /** @var DataBuilder\Compliance $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\Compliance::NICK);

        $builderData = $dataBuilder->getBuilderData();

        if (empty($builderData['responsible_person_ids'])) {
            return [];
        }

        return $builderData['responsible_person_ids'];
    }

    // ----------------------------------------

    private function collectMetaData(array $productData, array $publishProductData): void
    {
        $metaData = [];
        $metaData[DataBuilder\Title::NICK]['online_title'] = $productData['title'];
        $metaData[DataBuilder\Description::NICK]['online_description']
            = \M2E\TikTokShop\Model\Product::createOnlineDescription($productData['description']);
        $metaData[DataBuilder\Categories::NICK]['online_category_id'] = $productData['category_id'];
        $metaData[DataBuilder\Categories::NICK]['online_category_data']
            = json_encode($productData['product_attributes'], JSON_THROW_ON_ERROR);
        $metaData[DataBuilder\Brand::NICK]['brand_id'] = $productData['brand_id'];

        $publishSkusBySku = [];
        foreach ($publishProductData['skus'] as $item) {
            $publishSkusBySku[$item['seller_sku']] = $item;
        }

        foreach ($productData['skus'] as $skuData) {
            $sellerSku = $skuData['seller_sku'];
            $metaData[DataBuilder\VariantSku::NICK][$sellerSku] = [
                'online_sku' => $sellerSku,
                'online_price' => (float)($skuData['price']['amount'] ?? 0),
                'online_qty' => $publishSkusBySku[$sellerSku]['inventory']['quantity'] ?? 0,
                'online_image' => $this->findImageHash($skuData['sales_attributes'] ?? []),
                DataBuilder\VariantSku\Collection::METADATA_KEY_ONLINE_IDENTIFIER => $this->findIdentifier($skuData['identifier_code'] ?? []),
            ];
        }

        $metaData[DataBuilder\Compliance::NICK]['online_manufacturer_id'] = reset($publishProductData['manufacturer_ids']);
        $metaData[DataBuilder\Compliance::NICK]['online_responsible_person_ids'] = $publishProductData['responsible_person_ids'];

        $this->metaData = $metaData;
    }

    private function findImageHash(array $skuSalesAttributes): ?string
    {
        if (empty($skuSalesAttributes)) {
            return null;
        }

        foreach ($skuSalesAttributes as $salesAttribute) {
            $image = $this->imageRepository->findByUri($salesAttribute['sku_img']['uri'] ?? '');
            if ($image === null) {
                continue;
            }

            return $image->getHash();
        }

        return null;
    }

    private function findIdentifier(array $identifierData): array
    {
        if (empty($identifierData)) {
            return [];
        }

        return [
            DataBuilder\VariantSku\Collection::METADATA_KEY_ONLINE_IDENTIFIER_ID => $identifierData['code'],
            DataBuilder\VariantSku\Collection::METADATA_KEY_ONLINE_IDENTIFIER_TYPE => $identifierData['type'],
        ];
    }

    // ----------------------------------------

    private function getDataBuilder(string $nick): AbstractDataBuilder
    {
        return $this->dataBuilderFactory->create(
            $nick,
            $this->getListingProduct(),
            \M2E\TikTokShop\Model\Product::ACTION_LIST,
            $this->getConfigurator(),
            $this->getVariantSettings(),
            $this->getParams()
        );
    }
}
