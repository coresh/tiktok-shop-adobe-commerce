<?php

namespace M2E\TikTokShop\Model\Product;

use M2E\TikTokShop\Helper\Magento\Attribute;
use M2E\TikTokShop\Model\Exception\Logic;
use M2E\TikTokShop\Model\Listing;
use M2E\TikTokShop\Model\Magento\Product;
use M2E\TikTokShop\Model\Magento\Product\Cache;
use M2E\TikTokShop\Model\Template\SellingFormat;

class PriceCalculator
{
    public const MODE_NONE = 0;
    public const MODE_PRODUCT = 1;
    public const MODE_SPECIAL = 2;
    public const MODE_ATTRIBUTE = 3;
    public const MODE_TIER = 4;

    /**
     * @var null|array
     */
    private ?array $source = null;

    private array $sourceModeMapping = [
        self::MODE_NONE => \M2E\TikTokShop\Model\Template\SellingFormat::PRICE_MODE_NONE,
        self::MODE_PRODUCT => \M2E\TikTokShop\Model\Template\SellingFormat::PRICE_MODE_PRODUCT,
        self::MODE_SPECIAL => \M2E\TikTokShop\Model\Template\SellingFormat::PRICE_MODE_SPECIAL,
        self::MODE_ATTRIBUTE => \M2E\TikTokShop\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE,
        self::MODE_TIER => \M2E\TikTokShop\Model\Template\SellingFormat::PRICE_MODE_TIER,
    ];

    private \M2E\TikTokShop\Model\ProductInterface $product;

    /** @var null|string */
    private $coefficient = null;
    /** @var array */
    private $modifier = [];
    /** @var \M2E\TikTokShop\Model\Magento\Product\Cache|null */
    private $attributeSourceProduct;

    /**
     * @var null|float
     */
    private $vatPercent = null;

    /**
     * @var null|float
     */
    private $productValueCache = null;
    private \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory;
    private \M2E\TikTokShop\Model\Currency $currency;
    private \M2E\TikTokShop\Helper\Magento\Attribute $attributeHelper;

    public function __construct(
        \M2E\TikTokShop\Model\ProductInterface $product,
        \M2E\TikTokShop\Model\Currency $currency,
        \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentorProductFactory,
        \M2E\TikTokShop\Helper\Magento\Attribute $attributeHelper
    ) {
        $this->ourMagentoProductFactory = $ourMagentorProductFactory;
        $this->product = $product;
        $this->attributeHelper = $attributeHelper;
        $this->currency = $currency;
    }

    public function setSource(array $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @param null|string $key
     *
     * @return array|mixed
     * @throws Logic
     */
    private function getSource($key = null)
    {
        if (empty($this->source)) {
            throw new Logic('Initialize all parameters first.');
        }

        if ($key === null) {
            return $this->source;
        }

        return isset($this->source[$key]) ? $this->source[$key] : null;
    }

    // ---------------------------------------

    public function setSourceModeMapping(array $mapping)
    {
        $this->sourceModeMapping = $mapping;

        return $this;
    }

    private function getSourceMode()
    {
        if (!in_array($this->getSource('mode'), $this->sourceModeMapping)) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown source mode.');
        }

        return array_search($this->getSource('mode'), $this->sourceModeMapping);
    }

    // ---------------------------------------

    /**
     * @param string $value
     *
     * @return PriceCalculator
     */
    public function setCoefficient($value)
    {
        $this->coefficient = $value;

        return $this;
    }

    /**
     * @return string
     */
    private function getCoefficient()
    {
        return $this->coefficient;
    }

    public function setModifier(array $value): self
    {
        $this->modifier = $value;

        return $this;
    }

    /**
     * @return array
     */
    private function getModifier(): array
    {
        return $this->modifier;
    }

    /**
     * @param \M2E\TikTokShop\Model\Magento\Product\Cache|null $attributeSourceProduct
     *
     * @return \M2E\TikTokShop\Model\Product\PriceCalculator
     */
    public function setAttributeSourceProduct(?Cache $attributeSourceProduct): PriceCalculator
    {
        $this->attributeSourceProduct = $attributeSourceProduct;

        return $this;
    }

    /**
     * @return \M2E\TikTokShop\Model\Magento\Product\Cache|null
     */
    public function getAttributeSourceProduct(): ?Cache
    {
        return $this->attributeSourceProduct;
    }

    // ---------------------------------------

    public function setVatPercent($value)
    {
        $this->vatPercent = $value;

        return $this;
    }

    /**
     * @return float|null
     */
    private function getVatPercent()
    {
        return $this->vatPercent;
    }

    private function getListing(): Listing
    {
        return $this->product->getListing();
    }

    private function getMagentoProduct(): Cache
    {
        return $this->product->getMagentoProduct();
    }

    // ----------------------------------------

    public function getProductValue()
    {
        if ($this->isSourceModeNone()) {
            return 0;
        }

        $this->setAttributeSourceProduct($this->getMagentoProduct());
        $value = $this->getProductBaseValue();

        return $this->prepareFinalValue($value);
    }

    private function getProductBaseValue()
    {
        if ($this->productValueCache !== null) {
            return $this->productValueCache;
        }

        if ($this->isSourceModeProduct()) {
            if ($this->getMagentoProduct()->isConfigurableType()) {
                $value = $this->getConfigurableProductValue($this->getMagentoProduct());
            } elseif ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedProductValue($this->getMagentoProduct());
            } elseif (
                $this->getMagentoProduct()->isBundleType()
                && $this->getMagentoProduct()->isBundlePriceTypeDynamic()
            ) {
                $value = $this->getBundleProductDynamicValue($this->getMagentoProduct());
            } else {
                $value = $this->convertValueFromStoreToShop($this->getMagentoProduct()->getPrice());
            }
        } elseif ($this->isSourceModeSpecial()) {
            if ($this->getMagentoProduct()->isConfigurableType()) {
                $value = $this->getConfigurableProductValue($this->getMagentoProduct());
            } elseif ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedProductValue($this->getMagentoProduct());
            } elseif (
                $this->getMagentoProduct()->isBundleType()
                && $this->getMagentoProduct()->isBundlePriceTypeDynamic()
            ) {
                $value = $this->getBundleProductDynamicSpecialValue($this->getMagentoProduct());
            } else {
                $value = $this->getExistedProductSpecialValue($this->getMagentoProduct());
            }
        } elseif ($this->isSourceModeAttribute()) {
            if ($this->getMagentoProduct()->isConfigurableType()) {
                if (
                    $this->getSource('attribute') == Attribute::PRICE_CODE
                    || $this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE
                ) {
                    $value = $this->getConfigurableProductValue($this->getMagentoProduct());
                } else {
                    $value = $this->attributeHelper->convertAttributeTypePriceFromStoreToShop(
                        $this->getMagentoProduct(),
                        $this->getSource('attribute'),
                        $this->getCurrencyForPriceConvert(),
                        $this->getListing()->getStoreId()
                    );
                }
            } elseif ($this->getMagentoProduct()->isGroupedType()) {
                if (
                    $this->getSource('attribute') == Attribute::PRICE_CODE
                    || $this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE
                ) {
                    $value = $this->getGroupedProductValue($this->getMagentoProduct());
                } else {
                    $value = $this->attributeHelper->convertAttributeTypePriceFromStoreToShop(
                        $this->getMagentoProduct(),
                        $this->getSource('attribute'),
                        $this->getCurrencyForPriceConvert(),
                        $this->getListing()->getStoreId()
                    );
                }
            } elseif (
                $this->getMagentoProduct()->isBundleType()
                && (
                    $this->getMagentoProduct()->isBundlePriceTypeDynamic()
                    || (
                        $this->getMagentoProduct()->isBundlePriceTypeFixed()
                        && $this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE
                    )
                )
            ) {
                if (
                    $this->getMagentoProduct()->isBundlePriceTypeFixed()
                    && $this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE
                ) {
                    $value = $this->getExistedProductSpecialValue($this->getMagentoProduct());
                } else {
                    if ($this->getSource('attribute') == Attribute::PRICE_CODE) {
                        $value = $this->getBundleProductDynamicValue($this->getMagentoProduct());
                    } elseif ($this->getSource('attribute') == Attribute::SPECIAL_PRICE_CODE) {
                        $value = $this->getBundleProductDynamicSpecialValue($this->getMagentoProduct());
                    } else {
                        $value = $this->attributeHelper->convertAttributeTypePriceFromStoreToShop(
                            $this->getMagentoProduct(),
                            $this->getSource('attribute'),
                            $this->getCurrencyForPriceConvert(),
                            $this->getListing()->getStoreId()
                        );
                    }
                }
            } else {
                $value = $this->attributeHelper->convertAttributeTypePriceFromStoreToShop(
                    $this->getMagentoProduct(),
                    $this->getSource('attribute'),
                    $this->getCurrencyForPriceConvert(),
                    $this->getListing()->getStoreId()
                );
            }
        } elseif ($this->isSourceModeTier()) {
            if ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedTierValue($this->getMagentoProduct());
            } elseif ($this->getMagentoProduct()->isBundleType()) {
                if ($this->getMagentoProduct()->isBundlePriceTypeDynamic()) {
                    $value = $this->getBundleTierDynamicValue($this->getMagentoProduct());
                } else {
                    $value = $this->getBundleTierFixedValue($this->getMagentoProduct());
                }
            } else {
                $value = $this->getExistedProductTierValue($this->getMagentoProduct());
            }
        } else {
            throw new Logic('Unknown Mode in Database.');
        }

        return $this->productValueCache = !is_array($value) ? (float)$value : $value;
    }

    private function getExistedProductSpecialValue(Product $product): float
    {
        $value = (float)$product->getSpecialPrice();

        if ($value <= 0) {
            return $this->convertValueFromStoreToShop($product->getPrice());
        }

        return $this->convertValueFromStoreToShop($value);
    }

    private function getExistedProductTierValue(Product $product)
    {
        $tierPrice = $product->getTierPrice(
            $this->getSource('tier_website_id'),
            $this->getSource('tier_customer_group_id'),
        );

        foreach ($tierPrice as $qty => $value) {
            $tierPrice[$qty] = $this->convertValueFromStoreToShop($value);
        }

        return $tierPrice;
    }

    // ---------------------------------------

    private function getConfigurableProductValue(Product $product)
    {
        $value = 0;

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();

        /** @var \Magento\Catalog\Model\Product $childProduct */
        foreach ($productTypeInstance->getUsedProducts($product->getProduct()) as $childProduct) {
            $childProduct = $this->ourMagentoProductFactory->create()->setProduct($childProduct);

            $variationValue = (float)$childProduct->getSpecialPrice();
            $variationValue <= 0 && $variationValue = (float)$childProduct->getPrice();

            if ($variationValue < $value || $value == 0) {
                $value = $variationValue;
            }
        }

        return $value;
    }

    /**
     * @param \M2E\TikTokShop\Model\Magento\Product $product
     *
     * @return double
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getGroupedProductValue(Product $product)
    {
        $value = 0;
        foreach ($product->getTypeInstance()->getAssociatedProducts($product->getProduct()) as $childProduct) {
            /** @var \Magento\Catalog\Model\Product $childProduct */
            $variationValue = (float)$childProduct->getSpecialPrice();
            $variationValue <= 0 && $variationValue = (float)$childProduct->getPrice();
            $value = $variationValue;
        }

        return $value;
    }

    private function getBundleProductDynamicValue(Product $product)
    {
        $value = 0;

        $variationsData = $product->getVariationInstance()->getVariationsTypeStandard();

        foreach ($variationsData['variations'] as $variation) {
            $variationValue = 0;

            foreach ($variation as $option) {
                $childProduct = $this->ourMagentoProductFactory->createByProductId((int)$option['product_id']);

                $optionValue = (float)$childProduct->getSpecialPrice();
                $optionValue <= 0 && $optionValue = (float)$childProduct->getPrice();

                $variationValue += $optionValue;
            }

            if ($variationValue < $value || $value == 0) {
                $value = $variationValue;
            }
        }

        return $value;
    }

    private function getBundleProductDynamicSpecialValue(Product $product)
    {
        $value = $this->getBundleProductDynamicValue($product);

        if ($value <= 0 || !$product->isSpecialPriceActual()) {
            return $value;
        }

        $percent = (float)$product->getProduct()->getSpecialPrice();

        return round((($value * $percent) / 100), 2);
    }

    private function getGroupedTierValue(\M2E\TikTokShop\Model\Magento\Product $product)
    {
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();

        $lowestVariationValue = null;
        $resultChildProduct = null;

        /** @var \Magento\Catalog\Model\Product $childProduct */
        foreach ($productTypeInstance->getAssociatedProducts($product->getProduct()) as $childProduct) {
            $childProduct = $this->ourMagentoProductFactory->create()->setProduct($childProduct);

            $variationValue = (float)$childProduct->getSpecialPrice();
            $variationValue <= 0 && $variationValue = (float)$childProduct->getPrice();

            if ($variationValue < $lowestVariationValue || $lowestVariationValue === null) {
                $lowestVariationValue = $variationValue;
                $resultChildProduct = $childProduct;
            }
        }

        if ($resultChildProduct === null) {
            return null;
        }

        return $this->getExistedProductTierValue($resultChildProduct);
    }

    private function getBundleTierFixedValue(\M2E\TikTokShop\Model\Magento\Product $product)
    {
        return $this->calculateBundleTierValue($product, $product->getPrice());
    }

    private function getBundleTierDynamicValue(\M2E\TikTokShop\Model\Magento\Product $product)
    {
        return $this->calculateBundleTierValue($product, $this->getBundleProductDynamicValue($product));
    }

    //########################################

    private function prepareFinalValue($value)
    {
        if ($this->getCoefficient() !== null) {
            if (!$this->isSourceModeTier()) {
                $value = $this->modifyValueByCoefficient($value);
            } else {
                foreach ($value as $qty => $price) {
                    $value[$qty] = $this->modifyValueByCoefficient($price);
                }
            }
        }

        /** @psalm-suppress RedundantCondition */
        if ($this->getModifier() !== null) {
            if (!$this->isSourceModeTier()) {
                $value = $this->modifyValueByModifier($value);
            } else {
                foreach ($value as $qty => $price) {
                    $value[$qty] = $this->modifyValueByModifier($price);
                }
            }
        }

        if ($this->getVatPercent() !== null) {
            if (!$this->isSourceModeTier()) {
                $value = $this->increaseValueByVatPercent($value);
            } else {
                foreach ($value as $qty => $price) {
                    $value[$qty] = $this->increaseValueByVatPercent($price);
                }
            }
        }

        if (!$this->isSourceModeTier()) {
            $value < 0 && $value = 0;
            $value = round($value, 2);
        } else {
            foreach ($value as $qty => $price) {
                $price < 0 && $value[$qty] = 0;
                $value[$qty] = round($value[$qty], 2);
            }
        }

        return $value;
    }

    // ---------------------------------------

    private function modifyValueByCoefficient($value)
    {
        if ($value <= 0) {
            return $value;
        }

        $coefficient = $this->getCoefficient();

        if (is_string($coefficient)) {
            $coefficient = trim($coefficient);
        }

        if (!$coefficient) {
            return $value;
        }

        if (strpos($coefficient, '%') !== false) {
            $coefficient = str_replace('%', '', $coefficient);

            if (preg_match('/^[+-]/', $coefficient)) {
                return $value + $value * (float)$coefficient / 100;
            }

            return $value * (float)$coefficient / 100;
        }

        if (preg_match('/^[+-]/', $coefficient)) {
            return $value + (float)$coefficient;
        }

        return $value * (float)$coefficient;
    }

    /**
     * @param $value
     *
     * @return float
     */
    private function modifyValueByModifier($value)
    {
        if ($value <= 0) {
            return $value;
        }

        $result = $value;
        $modifier = $this->getModifier();
        $magentoProduct = $this->getAttributeSourceProduct();
        foreach ($modifier as $modification) {
            switch ($modification['mode']) {
                case SellingFormat::PRICE_MODIFIER_ABSOLUTE_INCREASE:
                    $result += (float)$modification['value'];
                    break;
                case SellingFormat::PRICE_MODIFIER_ABSOLUTE_DECREASE:
                    $result -= (float)$modification['value'];
                    break;
                case SellingFormat::PRICE_MODIFIER_PERCENTAGE_INCREASE:
                    $result *= 1 + (float)$modification['value'] / 100;
                    break;
                case SellingFormat::PRICE_MODIFIER_PERCENTAGE_DECREASE:
                    $result *= 1 - (float)$modification['value'] / 100;
                    break;
                case SellingFormat::PRICE_MODIFIER_ATTRIBUTE:
                    if (!$magentoProduct) {
                        break;
                    }

                    $attributeValue = $magentoProduct
                        ->getAttributeValue($modification['attribute_code']);
                    if (is_numeric($attributeValue)) {
                        $result += (float)$attributeValue;
                    }
            }
        }

        return $result;
    }

    private function increaseValueByVatPercent($value)
    {
        return $value + (($this->getVatPercent() * $value) / 100);
    }

    private function convertValueFromStoreToShop($value)
    {
        return $this->currency->convertPrice(
            $value,
            $this->getCurrencyForPriceConvert(),
            $this->getListing()->getStoreId()
        );
    }

    // ---------------------------------------

    private function getCurrencyForPriceConvert()
    {
        return $this->getListing()->getShop()->getCurrencyCode();
    }

    // ---------------------------------------

    private function calculateBundleTierValue(Product $product, $baseValue)
    {
        $tierPrice = $product->getTierPrice(
            $this->getSource('tier_website_id'),
            $this->getSource('tier_customer_group_id'),
        );

        $value = [];

        foreach ($tierPrice as $qty => $discount) {
            $value[$qty] = round(($baseValue - ($baseValue * (float)$discount) / 100), 2);
        }

        return $value;
    }

    // ---------------------------------------

    private function prepareOptionTitles($optionTitles)
    {
        foreach ($optionTitles as &$optionTitle) {
            $optionTitle = trim(
                \M2E\TikTokShop\Helper\Data::reduceWordsInString(
                    $optionTitle,
                    \M2E\TikTokShop\Helper\Component\TikTokShop::MAX_LENGTH_FOR_OPTION_VALUE
                )
            );
        }

        return $optionTitles;
    }

    private function prepareAttributeTitles($attributeTitles)
    {
        foreach ($attributeTitles as &$attributeTitle) {
            $attributeTitle = trim($attributeTitle);
        }

        return $attributeTitles;
    }

    //########################################

    private function isSourceModeNone(): bool
    {
        return $this->getSourceMode() == self::MODE_NONE;
    }

    private function isSourceModeProduct(): bool
    {
        return $this->getSourceMode() == self::MODE_PRODUCT;
    }

    private function isSourceModeSpecial(): bool
    {
        return $this->getSourceMode() == self::MODE_SPECIAL;
    }

    private function isSourceModeAttribute(): bool
    {
        return $this->getSourceMode() == self::MODE_ATTRIBUTE;
    }

    private function isSourceModeTier(): bool
    {
        return $this->getSourceMode() == self::MODE_TIER;
    }
}
