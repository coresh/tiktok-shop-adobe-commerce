<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder as ActionBuilder;

class Checker
{
    private \M2E\TikTokShop\Model\Image\Repository $imageRepository;
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Factory */
    private ActionBuilder\Factory $dataBuilderFactory;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\Factory $dataBuilderFactory,
        \M2E\TikTokShop\Model\Image\Repository $imageRepository
    ) {
        $this->imageRepository = $imageRepository;
        $this->dataBuilderFactory = $dataBuilderFactory;
    }

    public function isNeedReviseForTitle(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        if (!$this->isTitleReviseEnabled($listingProduct)) {
            return false;
        }

        return $listingProduct->getDescriptionTemplateSource()->getTitle() !== $listingProduct->getOnlineTitle();
    }

    public function isNeedReviseForDescription(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        if (!$this->isDescriptionReviseEnabled($listingProduct)) {
            return false;
        }

        $newOnlineDescription = \M2E\TikTokShop\Model\Product::createOnlineDescription(
            $listingProduct->getRenderedDescription(),
        );

        return $newOnlineDescription !== $listingProduct->getOnlineDescription();
    }

    public function isNeedReviseForBrand(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        if (!$this->isCategoriesReviseEnabled($listingProduct)) {
            return false;
        }

        /** @var ActionBuilder\Brand $actionDataBuilder */
        $actionDataBuilder = $this->dataBuilderFactory->create(ActionBuilder\Brand::NICK, $listingProduct);

        $actionDataBuilder->getBuilderData();

        $metadata = $actionDataBuilder->getMetaData()[ActionBuilder\Brand::NICK];

        return (string)$metadata['online_brand_name'] !== $listingProduct->getOnlineBrandName();
    }

    public function isNeedReviseForSizeChart(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        if (!$this->isCategoriesReviseEnabled($listingProduct)) {
            return false;
        }

        /** @var ActionBuilder\SizeChart $actionDataBuilder */
        $actionDataBuilder = $this->dataBuilderFactory->create(
            ActionBuilder\SizeChart::NICK,
            $listingProduct,
        );

        $actionDataBuilder->getBuilderData();

        $metadata = $actionDataBuilder->getMetaData()[ActionBuilder\SizeChart::NICK];

        $images = $this->imageRepository->findImagesWithProductRelation(
            $listingProduct->getId(),
            \M2E\TikTokShop\Model\Image::IMAGE_TYPE_SIZE_CHART,
        );

        if (empty($images) && !isset($metadata['image']['uri'])) {
            return false;
        }

        /** @var \M2E\TikTokShop\Model\Image $existImage */
        $existImage = reset($images);

        if ($existImage->getUri() === null) {
            return true;
        }

        return $metadata['image']['uri'] !== $existImage->getUri();
    }

    public function isNeedReviseForCertificates(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        if (!$this->isCategoriesReviseEnabled($listingProduct)) {
            return false;
        }

        /** @var ActionBuilder\CertificateImage $actionDataBuilder */
        $actionDataBuilder = $this->dataBuilderFactory->create(
            \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\CertificateImage::NICK,
            $listingProduct,
        );

        $actionDataBuilder->getBuilderData();

        $metadata = $actionDataBuilder->getMetaData()[ActionBuilder\CertificateImage::NICK];

        $existedImages = $this->imageRepository->findImagesWithProductRelation(
            $listingProduct->getId(),
            \M2E\TikTokShop\Model\Image::IMAGE_TYPE_CERTIFICATE,
        );

        $existedUri = [];
        foreach ($existedImages as $image) {
            $existedUri[] = $image->getUri();
        }

        foreach ($metadata as $certificateData) {
            foreach ($certificateData['images'] as $certificateImageData) {
                if ($certificateImageData['uri'] === null) {
                    return true;
                }

                if (!in_array($certificateImageData['uri'], $existedUri, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isNeedReviseForImages(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        if (!$this->isImagesReviseEnabled($listingProduct)) {
            return false;
        }

        /** @var ActionBuilder\Images $imagesDataBuilder */
        $imagesDataBuilder = $this->dataBuilderFactory->create(ActionBuilder\Images::NICK, $listingProduct);

        $imagesDataBuilder->getBuilderData();

        $metadata = $imagesDataBuilder->getMetaData()[ActionBuilder\Images::NICK];

        $images = $this->imageRepository->findImagesWithProductRelation(
            $listingProduct->getId(),
            \M2E\TikTokShop\Model\Image::IMAGE_TYPE_PRODUCT,
        );

        $existsUri = [];
        foreach ($images as $image) {
            $existsUri[] = $image->getUri();
        }

        if (empty($images) && empty($metadata)) {
            return false;
        }

        foreach ($metadata as $productImage) {
            if ($productImage['uri'] === null) {
                return true;
            }

            if (!in_array($productImage['uri'], $existsUri, true)) {
                return true;
            }
        }

        return false;
    }

    public function isNeedReviseForCategories(
        \M2E\TikTokShop\Model\Product $listingProduct
    ): bool {
        if (!$this->isCategoriesReviseEnabled($listingProduct)) {
            return false;
        }

        /** @var ActionBuilder\Categories $actionDataBuilder */
        $actionDataBuilder = $this->dataBuilderFactory->create(ActionBuilder\Categories::NICK, $listingProduct);

        $actionDataBuilder->getBuilderData();

        $metadata = $actionDataBuilder->getMetaData()[ActionBuilder\Categories::NICK];
        if ($metadata['online_category_id'] !== $listingProduct->getOnlineMainCategory()) {
            return true;
        }

        if ($metadata['online_category_data'] !== $listingProduct->getOnlineCategoryData()) {
            return true;
        }

        return false;
    }

    public function isNeedReviseForOther(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        if (!$this->isComplianceReviseEnabled($listingProduct)) {
            return false;
        }

        /** @var ActionBuilder\Compliance $actionDataBuilder */
        $actionDataBuilder = $this->dataBuilderFactory->create(ActionBuilder\Compliance::NICK, $listingProduct);

        $actionDataBuilder->getBuilderData();

        $metadata = $actionDataBuilder->getMetaData()[ActionBuilder\Compliance::NICK];
        if ($metadata['online_manufacturer_id'] !== $listingProduct->getOnlineManufacturerId()) {
            return true;
        }

        if ($metadata['online_responsible_person_id'] !== $listingProduct->getOnlineResponsiblePersonId()) {
            return true;
        }

        return false;
    }

    // ----------------------------------------

    private function isTitleReviseEnabled(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateTitle();
    }

    private function isDescriptionReviseEnabled(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateDescription();
    }

    private function isImagesReviseEnabled(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateImages();
    }

    private function isCategoriesReviseEnabled(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateCategories();
    }
    private function isComplianceReviseEnabled(\M2E\TikTokShop\Model\Product $listingProduct): bool
    {
        return $listingProduct->getSynchronizationTemplate()->isReviseUpdateCompliance();
    }
}
