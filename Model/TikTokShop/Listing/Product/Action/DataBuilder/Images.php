<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class Images extends AbstractDataBuilder
{
    public const NICK = 'Images';

    private \M2E\TikTokShop\Model\Image\Repository $imageRepository;

    private array $onlineData = [];

    public function __construct(
        \M2E\TikTokShop\Model\Image\Repository $imageRepository,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        parent::__construct($magentoAttributeHelper);

        $this->imageRepository = $imageRepository;
    }

    public function getBuilderData(): array
    {
        $listingProduct = $this->getListingProduct();
        $magentoProduct = $listingProduct->getMagentoProduct();

        $this->searchNotFoundAttributes($magentoProduct);

        $productImageSet = $listingProduct->getDescriptionTemplateSource()->getImageSet();

        $result = [];

        foreach ($productImageSet->getAll() as $productImage) {
            $image = $this->imageRepository->findByHashAndType(
                $productImage->getHash(),
                \M2E\TikTokShop\Model\Image::IMAGE_TYPE_PRODUCT
            );

            $result[] = [
                'uri' => $image !== null ? $image->getUri() : null,
                'nick' => $productImage->getHash(),
                'url' => $productImage->getUrl(),
            ];
        }

        $data = ['main_images' => $result];

        $this->onlineData = $result;

        $this->processNotFoundAttributes(
            (string)__('Main Image / Gallery Images'),
            $magentoProduct
        );

        $data['image_resize'] = $listingProduct->getDescriptionTemplate()->isUseImageResize();

        return $data;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => $this->onlineData,
        ];
    }
}
