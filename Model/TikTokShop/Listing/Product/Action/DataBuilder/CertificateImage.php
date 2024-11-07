<?php

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class CertificateImage extends AbstractDataBuilder
{
    public const NICK = 'CertificateImage';

    private \M2E\TikTokShop\Model\Image\Repository $imageRepository;
    private \M2E\TikTokShop\Model\Magento\Product\ImageFactory $magentoProductImageFactory;
    private \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository;

    private array $onlineData = [];

    public function __construct(
        \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository,
        \M2E\TikTokShop\Model\Image\Repository $imageRepository,
        \M2E\TikTokShop\Model\Magento\Product\ImageFactory $magentoProductImageFactory,
        \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        parent::__construct($magentoAttributeHelper);
        $this->imageRepository = $imageRepository;
        $this->magentoProductImageFactory = $magentoProductImageFactory;
        $this->attributeRepository = $attributeRepository;
    }

    public function getBuilderData(): array
    {
        $categoryId = $this->getListingProduct()->getTemplateCategoryId();

        $certificateAttributes = $this->attributeRepository->findByDictionaryId($categoryId, [
            CategoryAttribute::ATTRIBUTE_TYPE_CERTIFICATE,
        ]);

        if (count($certificateAttributes) === 0) {
            return [];
        }

        $magentoProduct = $this->getListingProduct()->getMagentoProduct();

        $this->searchNotFoundAttributes($magentoProduct);

        $imagesData = [];
        foreach ($certificateAttributes as $certificate) {
            if (
                $certificate->isValueModeNone()
                || $certificate->isValueModeRecommended()
            ) {
                continue;
            }

            $attributeCode = $certificate->getCustomAttributeValue();

            $attributeValue = $magentoProduct->getAttributeValue($attributeCode);

            if (empty($attributeValue)) {
                continue;
            }

            $magentoProductImage = $this->magentoProductImageFactory->create();
            $magentoProductImage->setUrl($attributeValue);

            $image = $this->imageRepository->findByHashAndType(
                $magentoProductImage->getHash(),
                \M2E\TikTokShop\Model\Image::IMAGE_TYPE_CERTIFICATE
            );

            $certificateId = $certificate->getData('attribute_id');

            $imagesData[$certificateId]['id'] = $certificateId;
            $imagesData[$certificateId]['images'][] = [
                'uri' => $image !== null ? $image->getUri() : null,
                'nick' => $magentoProductImage->getHash(),
                'url' => $magentoProductImage->getUrl(),
            ];
        }

        $this->processNotFoundAttributes((string)__('Certificates'), $magentoProduct);

        $response = array_values($imagesData);

        $this->onlineData = $response;

        return $response;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => $this->onlineData,
        ];
    }
}
