<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\GlobalProduct;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator;
use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;
use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings;

class CreateFromProduct
{
    /** @var DataBuilder\AbstractDataBuilder[] */
    private array $dataBuilders = [];

    private \M2E\TikTokShop\Model\GlobalProductFactory $globalProductFactory;
    private \M2E\TikTokShop\Model\GlobalProduct\VariantSkuFactory $globalVariantSkuFactory;
    private \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Factory $dataBuilderFactory;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;

    public function __construct(
        \M2E\TikTokShop\Model\GlobalProductFactory $globalProductFactory,
        \M2E\TikTokShop\Model\GlobalProduct\VariantSkuFactory $globalVariantSkuFactory,
        \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Factory $dataBuilderFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->globalProductFactory = $globalProductFactory;
        $this->globalVariantSkuFactory = $globalVariantSkuFactory;
        $this->globalProductRepository = $globalProductRepository;
        $this->dataBuilderFactory = $dataBuilderFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(\M2E\TikTokShop\Model\Product $product): \M2E\TikTokShop\Model\GlobalProduct
    {
        return $this->saveGlobalProductAndVariants(
            $this->createGlobalProduct($product),
            $this->createVariants($product)
        );
    }

    private function createGlobalProduct(\M2E\TikTokShop\Model\Product $product): \M2E\TikTokShop\Model\GlobalProduct
    {
        return $this->globalProductFactory
            ->create()
            ->setAccountId($product->getListing()->getAccountId())
            ->setMagentoProductId($product->getMagentoProductId())
            ->setSourceProductId($product->getId())
            ->setTitle($this->getTitle($product))
            ->setDescription($this->getDescription($product))
            ->setCategoryId($this->getCategoryId($product))
            ->setBrandId($this->getBrandId($product))
            ->setPackageDimensions($this->getPackageDimensions($product))
            ->setPackageWeight($this->getPackageWeight($product))
            ->setMainImages($this->getMainImages($product))
            ->setCertifications($this->getCertifications($product))
            ->setProductAttributes($this->getProductAttributes($product))
            ->setSizeChart($this->getSizeChart($product))
            ->setManufacturerIds($this->getManufacturerIds($product))
            ->setResponsiblePersonIds($this->getResponsiblePersonIds($product))
            ->setSourceLocale($this->getSourceLocale($product));
    }

    /**
     * @return \M2E\TikTokShop\Model\GlobalProduct\VariantSku[]
     */
    private function createVariants(\M2E\TikTokShop\Model\Product $product): array
    {
        $productVariants = $this->getBuilderDataWithVariantObject($product);

        $globalProductVariants = [];
        foreach ($productVariants as $variant) {
            $globalProductVariants[] = $this->globalVariantSkuFactory
                ->create()
                ->setMagentoProductId($variant['variant_object']->getMagentoProductId())
                ->setSalesAttributes($variant['sales_attributes'])
                ->setSellerSku((string)$variant['seller_sku'])
                ->setPrice($variant['price'])
                ->setIdentifierCode($variant['identifier_code'] ?? []);
        }

        return $globalProductVariants;
    }

    /**
     * @param \M2E\TikTokShop\Model\GlobalProduct\VariantSku[] $globalProductVariants
     */
    private function saveGlobalProductAndVariants(
        \M2E\TikTokShop\Model\GlobalProduct $globalProduct,
        array $globalProductVariants
    ): \M2E\TikTokShop\Model\GlobalProduct {
        $connection = $this->resourceConnection->getConnection();
        try {
            $connection->beginTransaction();

            $this->globalProductRepository->create($globalProduct);
            foreach ($globalProductVariants as $variant) {
                $variant->setGlobalProductId($globalProduct->getId());
                $this->globalProductRepository->createVariantSku($variant);
            }

            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();
        }

        return $globalProduct;
    }

    // ----------------------------------------

    private function getDataBuilder(
        string $nick,
        \M2E\TikTokShop\Model\Product $product
    ): DataBuilder\AbstractDataBuilder {
        if (!isset($this->dataBuilders[$nick])) {
            $this->dataBuilders[$nick] = $this->dataBuilderFactory->create(
                $nick,
                $product,
                $this->getConfigurator(),
                $this->getVariantSettings($product)
            );
        }

        return $this->dataBuilders[$nick];
    }

    private function getVariantSettings(\M2E\TikTokShop\Model\Product $product): VariantSettings
    {
        $variantSettings = new VariantSettings();
        foreach ($product->getVariants() as $variant) {
            $variantSettings->add($variant->getId(), VariantSettings::ACTION_ADD);
        }

        return $variantSettings;
    }

    private function getConfigurator(): Configurator
    {
        return (new Configurator())->enableAll();
    }

    // ----------------------------------------

    private function getTitle(\M2E\TikTokShop\Model\Product $product): string
    {
        /** @var DataBuilder\Title $builder */
        $builder = $this->getDataBuilder(DataBuilder\Title::NICK, $product);
        $builderData = $builder->getBuilderData();

        return (string)$builderData['title'];
    }

    private function getDescription(\M2E\TikTokShop\Model\Product $product): string
    {
        /** @var DataBuilder\Description $builder */
        $builder = $this->getDataBuilder(DataBuilder\Description::NICK, $product);
        $builderData = $builder->getBuilderData();

        return (string)$builderData['description'];
    }

    private function getCategoryId(\M2E\TikTokShop\Model\Product $product): string
    {
        /** @var DataBuilder\Categories $builder */
        $builder = $this->getDataBuilder(DataBuilder\Categories::NICK, $product);
        $builderData = $builder->getBuilderData();

        return (string)$builderData['category_id'];
    }

    private function getBrandId(\M2E\TikTokShop\Model\Product $product): ?string
    {
        /** @var DataBuilder\Brand $builder */
        $builder = $this->getDataBuilder(DataBuilder\Brand::NICK, $product);
        $builderData = $builder->getBuilderData();

        if (empty($builderData['brand_id'])) {
            return null;
        }

        return (string)$builderData['brand_id'];
    }

    private function getPackageDimensions(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\ProductPackage $builder */
        $builder = $this->getDataBuilder(DataBuilder\ProductPackage::NICK, $product);
        $builderData = $builder->getBuilderData();

        return $builderData['dimensions'];
    }

    private function getPackageWeight(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\ProductPackage $builder */
        $builder = $this->getDataBuilder(DataBuilder\ProductPackage::NICK, $product);
        $builderData = $builder->getBuilderData();

        return $builderData['weight'];
    }

    private function getMainImages(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\Images $builder */
        $builder = $this->getDataBuilder(DataBuilder\Images::NICK, $product);
        $builderData = $builder->getBuilderData();

        $images = [];
        foreach ($builderData['main_images'] as $imageData) {
            if ($imageData['uri'] !== null) {
                $images[] = ['uri' => $imageData['uri']];
            }
        }

        return $images;
    }

    private function getCertifications(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\CertificateImage $builder */
        $builder = $this->getDataBuilder(DataBuilder\CertificateImage::NICK, $product);
        $builderData = $builder->getBuilderData();

        if ($builderData === []) {
            return [];
        }

        $uploaded = [];
        foreach ($builderData as $certificate) {
            $id = $certificate['id'];
            foreach ($certificate['images'] as $certificateImageData) {
                if ($certificateImageData['uri']) {
                    $uploaded[$id]['id'] = $id;
                    $uploaded[$id]['images'][] = [
                        'uri' => $certificateImageData['uri'],
                    ];
                }
            }
        }

        if ($uploaded !== []) {
            return array_values($uploaded);
        }

        return [];
    }

    private function getProductAttributes(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\Categories $builder */
        $builder = $this->getDataBuilder(DataBuilder\Categories::NICK, $product);
        $builderData = $builder->getBuilderData();

        return $builderData['product_attributes'];
    }

    private function getSizeChart(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\SizeChart $builder */
        $builder = $this->getDataBuilder(DataBuilder\SizeChart::NICK, $product);
        $builderData = $builder->getBuilderData();

        if ($builderData === []) {
            return [];
        }

        if (empty($builderData['image']['uri'])) {
            return [];
        }

        unset($builderData['image']['nick']);
        unset($builderData['image']['url']);

        return $builderData;
    }

    private function getManufacturerIds(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\Compliance $builder */
        $builder = $this->getDataBuilder(DataBuilder\Compliance::NICK, $product);
        $builderData = $builder->getBuilderData();

        if (empty($builderData['manufacturer_id'])) {
            return [];
        }

        return [$builderData['manufacturer_id']];
    }

    private function getResponsiblePersonIds(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\Compliance $builder */
        $builder = $this->getDataBuilder(DataBuilder\Compliance::NICK, $product);
        $builderData = $builder->getBuilderData();

        if (empty($builderData['responsible_person_ids'])) {
            return [];
        }

        return $builderData['responsible_person_ids'];
    }

    private function getSourceLocale(\M2E\TikTokShop\Model\Product $product): string
    {
        $shopRegion = $product->getListing()->getShop()->getRegion();

        return $shopRegion->getLocale();
    }

    private function getBuilderDataWithVariantObject(\M2E\TikTokShop\Model\Product $product): array
    {
        /** @var DataBuilder\VariantSku $builder */
        $builder = $this->getDataBuilder(DataBuilder\VariantSku::NICK, $product);
        $builderData = $builder->getBuilderData();

        $variantsBySku = [];
        foreach ($product->getVariants() as $variantSku) {
            $variantsBySku[$variantSku->getSku()] = $variantSku;
        }

        foreach ($builderData as &$variantData) {
            $variantSku = $variantData['seller_sku'];
            $variantData['variant_object'] = $variantsBySku[$variantSku];
        }

        return $builderData;
    }
}
