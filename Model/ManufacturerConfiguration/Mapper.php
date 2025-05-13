<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ManufacturerConfiguration;

class Mapper
{
    public const BRAND_MISSING = 1;
    public const GPSR_MISSING = 2;

    /** @var \M2E\TikTokShop\Model\ManufacturerConfiguration\BrandNameResolver */
    private BrandNameResolver $brandNameResolver;
    /** @var \M2E\TikTokShop\Model\ManufacturerConfiguration\Repository */
    private Repository $manufacturerConfigurationRepository;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \Magento\Framework\UrlInterface $url;

    public function __construct(
        BrandNameResolver $brandNameResolver,
        Repository $manufacturerConfigurationRepository,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->brandNameResolver = $brandNameResolver;
        $this->manufacturerConfigurationRepository = $manufacturerConfigurationRepository;
        $this->productRepository = $productRepository;
        $this->url = $url;
    }

    public function execute(\M2E\TikTokShop\Model\Product $product): MapperResult
    {
        if (
            !$product->getCategoryDictionary()->isRequiredManufacturer()
            && !$product->getCategoryDictionary()->isRequiredResponsiblePerson()
        ) {
            return MapperResult::newSuccess();
        }

        $resolverResult = $this->brandNameResolver->resolve($product);
        if ($resolverResult->isFail()) {
            return MapperResult::newFail(
                (string)__(
                    "Product was not listed: " .
                    "the required attribute 'Brand' is missing."
                ),
                self::BRAND_MISSING
            );
        }

        $manufacturerConfiguration = $this->manufacturerConfigurationRepository
            ->findByTitle($resolverResult->getBrandName());
        if ($manufacturerConfiguration === null) {
            return MapperResult::newFail(
                (string)__(
                    'Product was not listed: the required GPSR Manufacturer and Responsible Person ' .
                    'information is missing. Please ensure that all product manufacturers are properly ' .
                    'specified in the <a href="%link" target="_blank">GPSR settings</a> section',
                    [
                        'link' => $this->url->getUrl('m2e_tiktokshop/settings/index/', [
                            'activeTab' => \M2E\TikTokShop\Block\Adminhtml\Settings\Tabs::TAB_ID_GPSR,
                        ]),
                    ]
                ),
                self::GPSR_MISSING
            );
        }

        $product->setManufacturerConfigId($manufacturerConfiguration->getId());
        $this->productRepository->save($product);

        return MapperResult::newSuccess();
    }
}
