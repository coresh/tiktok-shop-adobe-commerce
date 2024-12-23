<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Validator;

use M2E\TikTokShop\Model\Category\CategoryAttribute;

class CertificatesImageUrlValidator implements ValidatorInterface
{
    use CategoryAttributeValueTrait;

    private \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository;

    /** @var \M2E\TikTokShop\Model\Category\Dictionary\AbstractAttribute[] */
    private array $dictionaryAttributes = [];
    /** @var \M2E\TikTokShop\Model\Category\CategoryAttribute[] */
    private array $certificateAttributes = [];

    public function __construct(
        \M2E\TikTokShop\Model\Category\Attribute\Repository $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator
    ): ?string {
        $certificates = $this->getDictionaryCertificates($product);
        foreach ($certificates as $certificate) {
            if (
                $certificate->isRequired()
                && !$this->isCertificateValid($product, $certificate->getId())
            ) {
                return (string)__(
                    'An invalid image URL is set for the Product Certificate "%1"',
                    $certificate->getName()
                );
            }
        }

        return null;
    }

    /**
     * @param \M2E\TikTokShop\Model\Product $product
     *
     * @return \M2E\TikTokShop\Model\Category\Dictionary\Attribute\CertificateAttribute[]
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getDictionaryCertificates(\M2E\TikTokShop\Model\Product $product): array
    {
        $dictionaryId = $product->getTemplateCategoryId();
        if (!isset($this->dictionaryAttributes[$dictionaryId])) {
            $dictionary = $product->getCategoryDictionary();
            $this->dictionaryAttributes[$dictionaryId] = $dictionary->getCertificationsAttributes();
        }

        return $this->dictionaryAttributes[$dictionaryId];
    }

    private function isCertificateValid(\M2E\TikTokShop\Model\Product $product, string $certificateId): bool
    {
        $attribute = $this->getCertificateAttributeById($product->getTemplateCategoryId(), $certificateId);
        if ($attribute !== null) {
            $value = $this->getAttributeValue($attribute, $product->getMagentoProduct());
            if (
                !empty($value)
                && !\M2E\TikTokShop\Helper\Data::isValidUrl($value)
            ) {
                return false;
            }
        }

        return true;
    }

    private function getCertificateAttributeById(int $categoryId, string $attributeId): ?CategoryAttribute
    {
        $attributes = $this->getCertificateAttributes($categoryId);

        return isset($attributes[$attributeId]) ? $attributes[$attributeId] : null;
    }

    private function getCertificateAttributes(int $categoryId): array
    {
        if (!isset($this->certificateAttributes[$categoryId])) {
            $this->certificateAttributes[$categoryId] = [];
            $attributes = $this->attributeRepository->findByDictionaryId($categoryId, [
                CategoryAttribute::ATTRIBUTE_TYPE_CERTIFICATE,
            ]);

            foreach ($attributes as $attribute) {
                $this->certificateAttributes[$categoryId][$attribute->getAttributeId()] = $attribute;
            }
        }

        return $this->certificateAttributes[$categoryId];
    }
}
