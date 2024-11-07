<?php

namespace M2E\TikTokShop\Model\Product;

class QtyCalculator
{
    /**
     * @var null|array
     */
    private ?array $source = null;

    private \M2E\TikTokShop\Model\ProductInterface $product;

    private int $productValueCache;
    private \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration;

    public function __construct(
        \M2E\TikTokShop\Model\ProductInterface $product,
        \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration
    ) {
        $this->product = $product;
        $this->moduleConfiguration = $moduleConfiguration;
    }

    // ----------------------------------------

    public function getProductValue(): int
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->productValueCache)) {
            return $this->productValueCache;
        }

        $value = $this->getClearProductValue();

        $value = $this->applySellingFormatTemplateModifications($value);
        if ($value < 0) {
            $value = 0;
        }

        return $this->productValueCache = (int)floor($value);
    }

    private function getClearProductValue(): int
    {
        switch ($this->getSource('mode')) {
            case \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_NUMBER:
                $value = (int)$this->getSource('value');
                break;

            case \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE:
                $value = (int)$this->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                break;

            case \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED:
                $value = $this->getMagentoProduct()->getQty(false);
                break;

            case \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_PRODUCT:
                $value = $this->getMagentoProduct()->getQty(true);
                break;

            default:
                throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown Mode in Database.');
        }

        return $value;
    }

    private function applySellingFormatTemplateModifications($value)
    {
        $mode = $this->getSource('mode');

        if (
            $mode != \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE &&
            $mode != \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED &&
            $mode != \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_PRODUCT
        ) {
            return $value;
        }

        $value = $this->applyValuePercentageModifications($value);
        $value = $this->applyValueMinMaxModifications($value);

        return $value;
    }

    private function applyValuePercentageModifications($value)
    {
        $percents = $this->getSource('qty_percentage');

        if ($value <= 0 || $percents < 0 || $percents == 100) {
            return $value;
        }

        $roundingFunction = $this->moduleConfiguration->getQtyPercentageRoundingGreater() ? 'ceil' : 'floor';

        return (int)$roundingFunction(($value / 100) * $percents);
    }

    private function applyValueMinMaxModifications($value)
    {
        if ($value <= 0 || !$this->getSource('qty_modification_mode')) {
            return $value;
        }

        $minValue = $this->getSource('qty_min_posted_value');
        $value < $minValue && $value = 0;

        $maxValue = $this->getSource('qty_max_posted_value');
        $value > $maxValue && $value = $maxValue;

        return $value;
    }

    /**
     * @param null|string $key
     *
     * @return array|mixed
     */
    private function getSource($key = null)
    {
        if ($this->source === null) {
            $this->source = $this->product->getSellingFormatTemplate()->getQtySource();
        }

        return ($key !== null && isset($this->source[$key])) ?
            $this->source[$key] : $this->source;
    }

    private function getMagentoProduct(): \M2E\TikTokShop\Model\Magento\Product\Cache
    {
        return $this->product->getMagentoProduct();
    }
}
