<?php

namespace M2E\TikTokShop\Model\Order\Item;

class OptionsFinder
{
    private \M2E\TikTokShop\Model\Magento\Product $magentoProduct;

    private array $channelOptions = [];
    private array $magentoOptions = [];
    private array $magentoValue = [];
    private array $channelLabels = [];
    private array $failedOptions = [];
    private array $optionsData = ['associated_options' => [], 'associated_products' => []];
    private ?bool $isNeedToReturnFirstOptionValues = null;
    private \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration;
    private \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration,
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper
    ) {
        $this->moduleConfiguration = $moduleConfiguration;
        $this->magentoProductHelper = $magentoProductHelper;
    }

    /**
     * @param \M2E\TikTokShop\Model\Magento\Product $magentoProduct
     *
     * @return $this
     */
    public function setProduct(\M2E\TikTokShop\Model\Magento\Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setChannelOptions(array $options = [])
    {
        $this->channelOptions = \M2E\Core\Helper\Data::toLowerCaseRecursive($options);

        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function addChannelOptions(array $options = [])
    {
        $this->channelOptions = $this->channelOptions + \M2E\Core\Helper\Data::toLowerCaseRecursive($options);

        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setMagentoOptions(array $options = [])
    {
        $this->magentoOptions = $options;

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function find()
    {
        $this->failedOptions = [];
        $this->optionsData = ['associated_options' => [], 'associated_products' => []];

        if ($this->getProductType() === null || empty($this->magentoOptions)) {
            return;
        }

        if ($this->magentoProductHelper->isGroupedType($this->getProductType())) {
            $associatedProduct = $this->getGroupedAssociatedProduct();

            if ($associatedProduct === null) {
                return;
            }

            $this->optionsData['associated_products'] = [$associatedProduct->getId()];

            return;
        }

        if (empty($this->channelOptions)) {
            $this->isNeedToReturnFirstOptionValues() && $this->matchFirstOptions();

            return;
        }

        $this->matchOptions();
    }

    /**
     * @return array
     */
    public function getOptionsData()
    {
        if (isset($this->optionsData['associated_products'])) {
            $this->optionsData['associated_products'] = $this->magentoProductHelper->prepareAssociatedProducts(
                $this->optionsData['associated_products'],
                $this->magentoProduct
            );
        }

        return $this->optionsData;
    }

    public function hasFailedOptions(): bool
    {
        return !empty($this->failedOptions);
    }

    /**
     * @return string
     */
    public function getOptionsNotFoundMessage()
    {
        if ($this->magentoProductHelper->isConfigurableType($this->getProductType())) {
            $message = 'There is no associated Product found for Configurable Product.';
        } elseif ($this->magentoProductHelper->isGroupedType($this->getProductType())) {
            $message = 'There is no associated Product found for Grouped Product.';
        } else {
            $message = sprintf(
                'Product Option(s) "%s" not found.',
                implode(', ', $this->failedOptions)
            );
        }

        return $message;
    }

    /**
     * @return array|null|string
     * @throws \InvalidArgumentException
     */
    private function getProductType()
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->magentoProduct)) {
            throw new \InvalidArgumentException('Magento Product was not set.');
        }

        $type = $this->magentoProduct->getTypeId();
        if (!in_array($type, $this->getAllowedProductTypes())) {
            throw new \InvalidArgumentException(sprintf('Product type "%s" is not supported.', $type));
        }

        return $type;
    }

    private function matchFirstOptions()
    {
        $options = [];
        $products = [];

        foreach ($this->magentoOptions as $magentoOption) {
            $optionId = $magentoOption['option_id'];
            $valueId = $magentoOption['values'][0]['value_id'];

            $options[$optionId] = $valueId;
            $products["{$optionId}::{$valueId}"] = $magentoOption['values'][0]['product_ids'];
        }

        $this->optionsData = [
            'associated_options' => $options,
            'associated_products' => $products,
        ];
    }

    private function matchOptions()
    {
        $options = [];
        $products = [];

        foreach ($this->magentoOptions as $magentoOption) {
            $this->channelLabels = [];
            $this->magentoValue = [];

            $magentoOption['labels'] = array_filter($magentoOption['labels']);
            if ($this->isOptionFailed($magentoOption)) {
                continue;
            }

            $this->appendOption($magentoOption, $options);
            $this->appendProduct($magentoOption, $products);
        }

        $this->optionsData = [
            'associated_options' => $options,
            'associated_products' => $products,
        ];
    }

    /**
     * @param array $magentoOption
     *
     * @return bool
     */
    private function isOptionFailed(array $magentoOption)
    {
        $this->findChannelLabels($magentoOption['labels']);

        if (empty($this->channelLabels)) {
            $this->failedOptions[] = array_shift($magentoOption['labels']);

            return true;
        }

        $this->findMagentoValue($magentoOption['values']);

        if (
            empty($this->magentoValue) ||
            !isset($this->magentoValue['value_id']) ||
            !isset($this->magentoValue['product_ids'])
        ) {
            $this->failedOptions[] = array_shift($magentoOption['labels']);

            return true;
        }

        return false;
    }

    /**
     * @param array $optionLabels
     */
    private function findChannelLabels(array $optionLabels)
    {
        $optionLabels = \M2E\Core\Helper\Data::toLowerCaseRecursive($optionLabels);

        foreach ($optionLabels as $label) {
            if (isset($this->channelOptions[$label])) {
                $this->channelLabels = ['labels' => $this->channelOptions[$label]];

                return;
            }
        }
    }

    /**
     * @param array $magentoOptionValues
     */
    private function findMagentoValue(array $magentoOptionValues)
    {
        foreach ($magentoOptionValues as $optionValue) {
            $valueLabels = \M2E\Core\Helper\Data::toLowerCaseRecursive($optionValue['labels']);

            foreach ((array)$this->channelLabels['labels'] as $channelOptionLabel) {
                if (in_array($channelOptionLabel, $valueLabels, true)) {
                    $this->magentoValue = $optionValue;

                    return;
                }
            }
        }
    }

    //########################################

    /**
     * @param array $magentoOption
     * @param array $options
     */
    private function appendOption(array $magentoOption, array &$options)
    {
        $optionId = $magentoOption['option_id'];
        $valueId = $this->magentoValue['value_id'];

        $options[$optionId] = $valueId;
    }

    /**
     * @param array $magentoOption
     * @param array $products
     */
    private function appendProduct(array $magentoOption, array &$products)
    {
        $optionId = $magentoOption['option_id'];
        $valueId = $this->magentoValue['value_id'];

        $products["{$optionId}::{$valueId}"] = $this->magentoValue['product_ids'];
    }

    private function getGroupedAssociatedProduct()
    {
        $variationName = array_shift($this->channelOptions);

        if (
            ($variationName === null || strlen(trim($variationName)) == 0) &&
            !$this->isNeedToReturnFirstOptionValues()
        ) {
            return null;
        }

        foreach ($this->magentoOptions as $option) {
            // return product if it's name is equal to variation name
            if ($variationName === null || trim(strtolower($option->getName())) === trim(strtolower($variationName))) {
                return $option;
            }
        }

        return null;
    }

    private function isNeedToReturnFirstOptionValues(): bool
    {
        if ($this->isNeedToReturnFirstOptionValues !== null) {
            return $this->isNeedToReturnFirstOptionValues;
        }

        $configValue = (bool)$this->moduleConfiguration->getCreateWithFirstProductOptionsWhenVariationUnavailable();

        return $this->isNeedToReturnFirstOptionValues = $configValue;
    }

    private function getAllowedProductTypes()
    {
        return $this->magentoProductHelper->getOriginKnownTypes();
    }
}
