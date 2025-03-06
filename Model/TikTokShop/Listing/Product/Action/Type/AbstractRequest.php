<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

abstract class AbstractRequest extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\AbstractRequest
{
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\AbstractDataBuilder[] */
    private array $dataBuilders = [];

    private DataBuilder\Factory $dataBuilderFactory;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Factory $dataBuilderFactory
    ) {
        $this->dataBuilderFactory = $dataBuilderFactory;
    }

    public function getRequestData(): array
    {
        $data = $this->getActionData();

        $this->collectMetadata();
        $this->collectDataBuildersWarningMessages();

        return $data;
    }

    abstract protected function getActionData(): array;

    private function collectMetadata(): void
    {
        foreach ($this->dataBuilders as $dataBuilder) {
            $this->metaData = array_merge($this->metaData, $dataBuilder->getMetaData());
        }
    }

    private function collectDataBuildersWarningMessages(): void
    {
        foreach ($this->dataBuilders as $dataBuilder) {
            $messages = $dataBuilder->getWarningMessages();

            foreach ($messages as $message) {
                $this->addWarningMessage($message);
            }
        }
    }

    // ----------------------------------------

    private function getDataBuilder(
        string $nick
    ): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\AbstractDataBuilder {
        if (!isset($this->dataBuilders[$nick])) {
            $this->dataBuilders[$nick] = $this->dataBuilderFactory->create(
                $nick,
                $this->getListingProduct(),
                $this->getConfigurator(),
                $this->getVariantSettings(),
                $this->getParams(),
                $this->getCachedData(),
            );
        }

        return $this->dataBuilders[$nick];
    }

    // ----------------------------------------

    protected function appendTitle(array $request): array
    {
        if (!$this->getConfigurator()->isTitleAllowed()) {
            return $request;
        }

        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Title $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\Title::NICK);
        $titleData = $dataBuilder->getBuilderData();

        $request['product_data']['title'] = $titleData['title'];

        return $request;
    }

    protected function appendDescription(array $request): array
    {
        if (!$this->getConfigurator()->isDescriptionAllowed()) {
            return $request;
        }

        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Description $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\Description::NICK);
        $descriptionData = $dataBuilder->getBuilderData();

        $request['product_data']['description'] = $descriptionData['description'];

        return $request;
    }

    protected function getProductPackageData(): array
    {
        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\ProductPackage $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\ProductPackage::NICK);

        return $dataBuilder->getBuilderData();
    }

    protected function appendCertificateData(array $request): array
    {
        if (!$this->getConfigurator()->isCategoriesAllowed()) {
            return $request;
        }

        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\CertificateImage $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\CertificateImage::NICK);
        $certificates = $dataBuilder->getBuilderData();

        if ($certificates === []) {
            return $request;
        }

        $uploaded = [];
        $notUploaded = [];
        foreach ($certificates as $certificate) {
            $id = $certificate['id'];
            foreach ($certificate['images'] as $certificateImageData) {
                if ($certificateImageData['uri']) {
                    $uploaded[$id]['id'] = $id;
                    $uploaded[$id]['images'][] = [
                        'uri' => $certificateImageData['uri'],
                    ];
                } else {
                    $notUploaded[] = [
                        'id' => $id,
                        'nick' => $certificateImageData['nick'],
                        'url' => $certificateImageData['url'],
                    ];
                }
            }
        }

        if ($uploaded !== []) {
            $request['product_data']['certifications'] = array_values($uploaded);
        }

        if ($notUploaded !== []) {
            $request['certifications'] = $notUploaded;
        }

        return $request;
    }

    protected function appendSizeChartData(array $request): array
    {
        if (!$this->getConfigurator()->isCategoriesAllowed()) {
            return $request;
        }

        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\SizeChart $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\SizeChart::NICK);
        $sizeChart = $dataBuilder->getBuilderData();

        if ($sizeChart !== []) {
            $request['product_data']['size_chart'] = $sizeChart;
        }

        return $request;
    }

    protected function appendBrandData(array $request): array
    {
        if (!$this->getConfigurator()->isCategoriesAllowed()) {
            return $request;
        }

        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Brand $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\Brand::NICK);

        $brandData = $dataBuilder->getBuilderData();

        if (!empty($brandData['brand_id'])) {
            $request['product_data']['brand_id'] = $brandData['brand_id'];
        } elseif (!empty($brandData['brand_name'])) {
            $request['brand_name'] = $brandData['brand_name'];
        }

        return $request;
    }

    protected function appendImagesData(array $request): array
    {
        if (!$this->getConfigurator()->isImagesAllowed()) {
            return $request;
        }

        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Images $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\Images::NICK);
        $imagesData = $dataBuilder->getBuilderData();

        $images = [];
        foreach ($imagesData['main_images'] as $imageData) {
            if ($imageData['uri'] !== null) {
                $images[] = ['uri' => $imageData['uri']];
            } else {
                $images[] = [
                    'nick' => $imageData['nick'],
                    'url' => $imageData['url'],
                ];
            }
        }

        $request['product_data']['main_images'] = $images;
        $request['image_resize'] = $imagesData['image_resize'];

        return $request;
    }

    protected function appendCategoryData(array $request): array
    {
        if (!$this->getConfigurator()->isCategoriesAllowed()) {
            return $request;
        }

        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Categories $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\Categories::NICK);
        $categoryData = $dataBuilder->getBuilderData();

        $request['product_data']['category_id'] = $categoryData['category_id'];
        $request['product_data']['product_attributes'] = $categoryData['product_attributes'] ?? null;

        return $request;
    }

    protected function appendSkuItems(array $request): array
    {
        if (!$this->getConfigurator()->isVariantsAllowed()) {
            return $request;
        }

        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\VariantSku $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\VariantSku::NICK);
        $request['product_data']['skus'] = $dataBuilder->getBuilderData();

        return $request;
    }

    protected function appendPackageData(array $request): array
    {
        $packageData = $this->getProductPackageData();

        if ($packageData['weight'] !== []) {
            $request['product_data']['package_weight'] = [
                'value' => $packageData['weight']['value'],
                'unit' => $packageData['weight']['unit'],
            ];
        }

        if ($packageData['dimensions'] !== []) {
            $request['product_data']['package_dimensions'] = [
                'length' => $packageData['dimensions']['length'],
                'width' => $packageData['dimensions']['width'],
                'height' => $packageData['dimensions']['height'],
                'unit' => $packageData['dimensions']['unit'],
            ];
        }

        return $request;
    }

    protected function appendComplianceData(array $request): array
    {
        /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Compliance $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\Compliance::NICK);

        $builderData = $dataBuilder->getBuilderData();

        if (
            $builderData['manufacturer_id'] === null
            || $builderData['responsible_person_ids'] === null
        ) {
            return $request;
        }

        $request['product_data']['manufacturer_ids'] = [$builderData['manufacturer_id']];
        $request['product_data']['responsible_person_ids'] = $builderData['responsible_person_ids'];

        return $request;
    }

    protected function appendGlobalProductData(array $request): array
    {
        /** @var DataBuilder\GlobalProduct $dataBuilder */
        $dataBuilder = $this->getDataBuilder(DataBuilder\GlobalProduct::NICK);

        $builderData = $dataBuilder->getBuilderData();
        $request['product_data'] = $builderData['product_data'];
        $request['publish_product_data'] = $builderData['publish_product_data'];

        return $request;
    }
}
