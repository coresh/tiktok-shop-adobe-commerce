<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Image;

class ImageService
{
    private Repository $imageRepository;
    private \M2E\TikTokShop\Model\Product\Image\Relation\Repository $relationRepository;
    private \M2E\TikTokShop\Model\ImageFactory $imageFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ImageFactory $imageFactory,
        Repository $imageRepository,
        \M2E\TikTokShop\Model\Product\Image\Relation\Repository $relationRepository
    ) {
        $this->imageFactory = $imageFactory;
        $this->imageRepository = $imageRepository;
        $this->relationRepository = $relationRepository;
    }

    public function addUploadedImage(
        int $listingProductId,
        Manager\UploadedImage $uploadedImage
    ): void {
        $image = $this->createAndSaveNewImage($uploadedImage);
        $this->linkImageToListingProduct($listingProductId, $image);
    }

    private function createAndSaveNewImage(Manager\UploadedImage $uploadedImage): \M2E\TikTokShop\Model\Image
    {
        $image = $this->imageFactory->create();
        $image->create(
            $uploadedImage->getType(),
            $uploadedImage->getUrl(),
            $uploadedImage->getNick(),
            $uploadedImage->getUri()
        );

        return $this->imageRepository->save($image);
    }

    public function createImageRelationIfNeed(int $listingProductId, string $uri): void
    {
        $relation = $this->relationRepository
            ->findByUriAndListingProductId($listingProductId, $uri);

        if ($relation !== null) {
            return;
        }

        $image = $this->imageRepository->findByUri($uri);

        $this->linkImageToListingProduct($listingProductId, $image);
    }

    private function linkImageToListingProduct(int $listingProductId, \M2E\TikTokShop\Model\Image $image)
    {
        $this->relationRepository->createIfNotExists(
            $listingProductId,
            $image->getId()
        );
    }
}
