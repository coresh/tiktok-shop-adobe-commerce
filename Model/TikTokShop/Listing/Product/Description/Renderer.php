<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Description;

class Renderer
{
    public const TTS_ATTRIBUTE_CODE_TITLE = 'title';
    public const TTS_ATTRIBUTE_CODE_PRICE = 'fixed_price';
    public const TTS_ATTRIBUTE_CODE_QTY = 'qty';

    private \M2E\TikTokShop\Model\Product $listingProduct;

    public function __construct(
        \M2E\TikTokShop\Model\Product $listingProduct
    ) {
        $this->listingProduct = $listingProduct;
    }

    public function parseTemplate(string $text): string
    {
        return $this->replaceTikTokShopAttributes($text);
    }

    private function replaceTikTokShopAttributes(string $text): string
    {
        preg_match_all("/#value\[(.+?)\]#/", $text, $matches);

        if (empty($matches[0])) {
            return $text;
        }

        $replaces = [];
        foreach ($matches[1] as $i => $attributeCode) {
            $value = $this->resolveAttributeValueByCode($attributeCode);

            if ($value !== '') {
                $replaces[$matches[0][$i]] = $value;
            }
        }

        if (empty($replaces)) {
            return $text;
        }

        return str_replace(array_keys($replaces), array_values($replaces), $text);
    }

    private function resolveAttributeValueByCode(string $attributeCode): string
    {
        switch ($attributeCode) {
            case self::TTS_ATTRIBUTE_CODE_TITLE:
                return $this->getTitle();
            case self::TTS_ATTRIBUTE_CODE_PRICE:
                return $this->getPrice();
            case self::TTS_ATTRIBUTE_CODE_QTY:
                return $this->getQty();
        }

        return '';
    }

    private function getTitle(): string
    {
        return $this->listingProduct->getDescriptionTemplateSource()->getTitle();
    }

    private function getPrice(): string
    {
        $price = $this->listingProduct->getFirstVariant()->getFixedPrice();
        if (empty($price)) {
            return 'N/A';
        }

        return sprintf('%01.2f', $price);
    }

    private function getQty(): string
    {
        return (string)$this->listingProduct->getFirstVariant()->getQty();
    }
}
