<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type;

class ImageResponseHandler
{
    private \M2E\TikTokShop\Model\Image\ImageService $imageService;

    public function __construct(
        \M2E\TikTokShop\Model\Image\ImageService $imageService
    ) {
        $this->imageService = $imageService;
    }

    public function handleResponse(
        int $listingProductId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData,
        \M2E\TikTokShop\Model\Connector\Response $response
    ): void {
        $this->handleProductImages($listingProductId, $requestData, $response);
        $this->handleSizeChartImages($listingProductId, $requestData, $response);
        $this->handleCertificationImages($listingProductId, $requestData, $response);
        $this->handleVariantImages($listingProductId, $requestData, $response);
    }

    private function handleProductImages(
        int $productId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData,
        \M2E\TikTokShop\Model\Connector\Response $response
    ): void {
        $uploadedImages = $response->getResponseData()['uploaded_images'] ?? null;
        $requestImages = $requestData->getData('product_data')['main_images'] ?? null;
        if ($uploadedImages === null || $requestImages === null) {
            return;
        }

        foreach ($uploadedImages as $uploadedImage) {
            if (
                empty($uploadedImage['nick'])
                || empty($uploadedImage['uri'])
            ) {
                continue;
            }

            foreach ($requestImages as $requestImage) {
                if (!isset($requestImage['nick'])) {
                    continue;
                }

                if ($requestImage['nick'] === $uploadedImage['nick']) {
                    $uploadedImage['url'] = $requestImage['url'];
                }
            }

            if (!isset($uploadedImage['url'])) {
                continue;
            }

            $image = new \M2E\TikTokShop\Model\Image\Manager\UploadedImage(
                $uploadedImage['nick'],
                $uploadedImage['uri'],
                $uploadedImage['url'],
                \M2E\TikTokShop\Model\Image::IMAGE_TYPE_PRODUCT
            );

            $this->imageService->addUploadedImage($productId, $image);
        }

        foreach ($requestImages as $requestImage) {
            if (empty($requestImage['uri'])) {
                continue;
            }

            $this->imageService->createImageRelationIfNeed($productId, $requestImage['uri']);
        }
    }

    private function handleSizeChartImages(
        int $productId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData,
        \M2E\TikTokShop\Model\Connector\Response $response
    ): void {
        $uploadedImage = $response->getResponseData()['upload_size_chart_image'] ?? [];
        $requestImage = $requestData->getData('product_data')['size_chart']['image'] ?? [];

        if (
            $uploadedImage !== []
            && !empty($uploadedImage['nick'])
            && !empty($uploadedImage['uri'])
            && !empty($requestImage['url'])
        ) {
            $image = new \M2E\TikTokShop\Model\Image\Manager\UploadedImage(
                $uploadedImage['nick'],
                $uploadedImage['uri'],
                $requestImage['url'],
                \M2E\TikTokShop\Model\Image::IMAGE_TYPE_SIZE_CHART
            );

            $this->imageService->addUploadedImage($productId, $image);
        }

        if (
            ($requestImage !== null && $uploadedImage === [])
            && !empty($requestImage['uri'])
        ) {
            $this->imageService->createImageRelationIfNeed($productId, $requestImage['uri']);
        }
    }

    private function handleCertificationImages(
        int $productId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData,
        \M2E\TikTokShop\Model\Connector\Response $response
    ): void {
        $uploadedImages = $response->getResponseData()['upload_certifications_images'] ?? [];
        $requestImages = $requestData->getData('certifications') ?? [];

        foreach ($uploadedImages as $uploadedImage) {
            if (
                empty($uploadedImage['nick'])
                || empty($uploadedImage['uri'])
            ) {
                continue;
            }

            foreach ($requestImages as $requestImage) {
                if ($requestImage['nick'] === $uploadedImage['nick']) {
                    $uploadedImage['url'] = $requestImage['url'];
                }
            }

            if (!isset($uploadedImage['url'])) {
                continue;
            }

            $image = new \M2E\TikTokShop\Model\Image\Manager\UploadedImage(
                $uploadedImage['nick'],
                $uploadedImage['uri'],
                $uploadedImage['url'],
                \M2E\TikTokShop\Model\Image::IMAGE_TYPE_CERTIFICATE
            );

            $this->imageService->addUploadedImage($productId, $image);
        }

        $existRequestImages = $requestData->getData('product_data')['certifications'] ?? [];
        foreach ($existRequestImages as $requestImage) {
            foreach ($requestImage['images'] ?? [] as $image) {
                if (empty($image['uri'])) {
                    continue;
                }

                $this->imageService->createImageRelationIfNeed($productId, $image['uri']);
            }
        }
    }

    private function handleVariantImages(
        int $listingProductId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData,
        \M2E\TikTokShop\Model\Connector\Response $response
    ) {
        $uploadedImages = $response->getResponseData()['upload_attributes_images'] ?? [];
        if ($uploadedImages === []) {
            return;
        }

        $requestImages = $requestData->getVariantImagesByNick();

        $savedUris = [];
        foreach ($uploadedImages as $uploadedImage) {
            if (
                empty($uploadedImage['nick'])
                || empty($uploadedImage['uri'])
            ) {
                continue;
            }

            if (in_array($uploadedImage['uri'], $savedUris)) {
                continue;
            }

            $uploadedImage['url'] = $requestImages[$uploadedImage['nick']] ?? '';

            if (empty($uploadedImage['url'])) {
                continue;
            }

            $image = new \M2E\TikTokShop\Model\Image\Manager\UploadedImage(
                $uploadedImage['nick'],
                $uploadedImage['uri'],
                $uploadedImage['url'],
                \M2E\TikTokShop\Model\Image::IMAGE_TYPE_VARIANT
            );

            $this->imageService->addUploadedImage($listingProductId, $image);

            $savedUris[] = $image->getUri();
        }

        foreach ($requestData->getVariantImagesUris() as $uri) {
            $this->imageService->createImageRelationIfNeed($listingProductId, $uri);
        }
    }
}
