<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class NonSalableFlag extends AbstractDataBuilder
{
    public const NICK = 'IsNotForSale';
    public const VALUE_CODE = 'is_not_for_sale';

    private ?bool $isNotForSale = null;

    public function getBuilderData(): array
    {
        if (!$this->isListingAction()) {
            $this->processNonSalableWarning();

            return [];
        }

        $this->isNotForSale = $this->getListingProduct()
                                   ->getListing()
                                   ->getTemplateSellingFormat()
                                   ->isNotForSale();

        return [
            self::VALUE_CODE => $this->isNotForSale,
        ];
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => $this->isNotForSale,
        ];
    }

    private function processNonSalableWarning(): void
    {
        $product = $this->getListingProduct();
        $sellingFormat = $product->getListing()->getTemplateSellingFormat();

        if (
            $this->isReviseAction()
            && ($product->isGift() !== $sellingFormat->isNotForSale())
        ) {
            $this->addWarningMessage(
                $this->buildNonSalableWarningMessage($product->isGift())
            );
        }
    }

    private function buildNonSalableWarningMessage(bool $isGiftProduct): string
    {
        return $isGiftProduct
            ? (string)__('Price was not revised because the Product is currently listed as non-sellable gift.')
            : (string)__(
                'Price was not revised because the \'Non-Sellable\' settings are currently enabled '
                . 'for this Product in Selling Policy.'
            );
    }
}
