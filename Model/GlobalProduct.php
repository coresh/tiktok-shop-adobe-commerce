<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Model\ResourceModel\GlobalProduct as GlobalProductResource;

class GlobalProduct extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    /** @var \M2E\TikTokShop\Model\GlobalProduct\VariantSku[] */
    private array $variantsBySku;
    /** @var \M2E\TikTokShop\Model\Product */
    private Product $sourceProduct;

    private \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\GlobalProduct\Repository $globalProductRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->globalProductRepository = $globalProductRepository;
        $this->productRepository = $productRepository;
    }

    public function setGlobalId(string $globalId): self
    {
        $this->setData(GlobalProductResource::COLUMN_GLOBAL_ID, $globalId);

        return $this;
    }

    public function getGlobalId(): ?string
    {
        $globalId = $this->getData(GlobalProductResource::COLUMN_GLOBAL_ID);
        if (empty($globalId)) {
            return null;
        }

        return (string)$globalId;
    }

    /**
     * @return \M2E\TikTokShop\Model\GlobalProduct\VariantSku[]
     */
    public function getGlobalVariants(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->variantsBySku)) {
            $variants = $this->globalProductRepository
                ->getVariantsByGlobalProductId($this->getId());

            foreach ($variants as $variant) {
                $this->variantsBySku[$variant->getSellerSku()] = $variant;
            }
        }

        return $this->variantsBySku;
    }

    public function findGlobalVariantBySku(
        string $sellerSku
    ): ?\M2E\TikTokShop\Model\GlobalProduct\VariantSku {
        $variants = $this->getGlobalVariants();

        return $variants[$sellerSku] ?? null;
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(GlobalProductResource::class);
    }

    // ----------------------------------------

    public function setAccountId(int $accountId): self
    {
        $this->setData(GlobalProductResource::COLUMN_ACCOUNT_ID, $accountId);

        return $this;
    }

    public function setMagentoProductId(int $magentoProductId): self
    {
        $this->setData(GlobalProductResource::COLUMN_MAGENTO_PRODUCT_ID, $magentoProductId);

        return $this;
    }

    public function setSourceProductId(int $sourceProductId): self
    {
        $this->setData(GlobalProductResource::COLUMN_SOURCE_PRODUCT_ID, $sourceProductId);

        return $this;
    }

    public function getSourceProductId(): int
    {
        return (int)$this->getData(GlobalProductResource::COLUMN_SOURCE_PRODUCT_ID);
    }

    public function getSourceProduct(): Product
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->sourceProduct)) {
            $this->sourceProduct = $this->productRepository->get($this->getSourceProductId());
        }
        return $this->sourceProduct;
    }

    public function setTitle(string $title): self
    {
        $this->setData(GlobalProductResource::COLUMN_TITLE, $title);

        return $this;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(GlobalProductResource::COLUMN_TITLE);
    }

    public function setDescription(string $description): self
    {
        $this->setData(GlobalProductResource::COLUMN_DESCRIPTION, $description);

        return $this;
    }

    public function getDescription(): string
    {
        return (string)$this->getData(GlobalProductResource::COLUMN_DESCRIPTION);
    }

    public function setCategoryId(string $categoryId): self
    {
        $this->setData(GlobalProductResource::COLUMN_CATEGORY_ID, $categoryId);

        return $this;
    }

    public function getCategoryId(): string
    {
        return (string)$this->getData(GlobalProductResource::COLUMN_CATEGORY_ID);
    }

    public function setBrandId(?string $brandId): self
    {
        $this->setData(GlobalProductResource::COLUMN_BRAND_ID, $brandId);

        return $this;
    }

    public function getBrandId(): ?string
    {
        $brandId = $this->getData(GlobalProductResource::COLUMN_BRAND_ID);
        if (empty($brandId)) {
            return null;
        }

        return (string)$brandId;
    }

    public function setPackageDimensions(array $packageDimensions): self
    {
        $this->setData(
            GlobalProductResource::COLUMN_PACKAGE_DIMENSIONS,
            json_encode($packageDimensions, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getPackageDimensions(): array
    {
        $data = $this->getData(GlobalProductResource::COLUMN_PACKAGE_DIMENSIONS);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setPackageWeight(array $packageWeight): self
    {
        $this->setData(
            GlobalProductResource::COLUMN_PACKAGE_WEIGHT,
            json_encode($packageWeight, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getPackageWeight(): array
    {
        $data = $this->getData(GlobalProductResource::COLUMN_PACKAGE_WEIGHT);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setMainImages(array $mainImages): self
    {
        $this->setData(
            GlobalProductResource::COLUMN_MAIN_IMAGES,
            json_encode($mainImages, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getMainImages(): array
    {
        $data = $this->getData(GlobalProductResource::COLUMN_MAIN_IMAGES);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setCertifications(array $certifications): self
    {
        $this->setData(
            GlobalProductResource::COLUMN_CERTIFICATIONS,
            json_encode($certifications, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getCertifications(): array
    {
        $data = $this->getData(GlobalProductResource::COLUMN_CERTIFICATIONS);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setProductAttributes(array $productAttributes): self
    {
        $this->setData(
            GlobalProductResource::COLUMN_PRODUCT_ATTRIBUTES,
            json_encode($productAttributes, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getProductAttributes(): array
    {
        $data = $this->getData(GlobalProductResource::COLUMN_PRODUCT_ATTRIBUTES);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setSizeChart(array $sizeChart): self
    {
        $this->setData(
            GlobalProductResource::COLUMN_SIZE_CHART,
            json_encode($sizeChart, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getSizeChart(): array
    {
        $data = $this->getData(GlobalProductResource::COLUMN_SIZE_CHART);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setManufacturerIds(array $manufacturerIds): self
    {
        $this->setData(
            GlobalProductResource::COLUMN_MANUFACTURER_IDS,
            json_encode($manufacturerIds, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getManufacturerIds(): array
    {
        $data = $this->getData(GlobalProductResource::COLUMN_MANUFACTURER_IDS);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setResponsiblePersonIds(array $responsiblePersonIds): self
    {
        $this->setData(
            GlobalProductResource::COLUMN_RESPONSIBLE_PERSON_IDS,
            json_encode($responsiblePersonIds, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    public function getResponsiblePersonIds(): array
    {
        $data = $this->getData(GlobalProductResource::COLUMN_RESPONSIBLE_PERSON_IDS);
        if (empty($data)) {
            return [];
        }

        return json_decode($data, true);
    }

    public function setSourceLocale(string $sourceLocale): self
    {
        $this->setData(GlobalProductResource::COLUMN_SOURCE_LOCALE, $sourceLocale);

        return $this;
    }

    public function getSourceLocale(): string
    {
        return (string)$this->getData(GlobalProductResource::COLUMN_SOURCE_LOCALE);
    }
}
