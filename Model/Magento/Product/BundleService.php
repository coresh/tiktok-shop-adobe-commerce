<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product;

class BundleService
{
    /**
     * @return array<\M2E\TikTokShop\Model\Magento\Product\Bundle\OptionWrapper>
     */
    public function getOptionsWithSelections(\M2E\TikTokShop\Model\Magento\Product $magentoProduct): array
    {
        return $this->wrapAndGetOptions($magentoProduct);
    }

    /**
     * @return array<string, array<\M2E\TikTokShop\Model\Magento\Product\Bundle\SelectionWrapper>>
     */
    public function getSelectionsGroupBySku(\M2E\TikTokShop\Model\Magento\Product $magentoProduct): array
    {
        $options = $this->wrapAndGetOptions($magentoProduct);

        $result = [];
        foreach ($options as $option) {
            foreach ($option->getSelections() as $selection) {
                $result[$selection->getSku()][] = $selection;
            }
        }

        return $result;
    }

    /**
     * @return array<\M2E\TikTokShop\Model\Magento\Product\Bundle\OptionWrapper>
     */
    private function wrapAndGetOptions(\M2E\TikTokShop\Model\Magento\Product $magentoProduct): array
    {
        $typeInstance = $magentoProduct->getTypeInstance();
        $product = $magentoProduct->getProduct();

        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
        $optionsCollection = $typeInstance->getOptionsCollection($product);

        $options = [];
        /** @var \Magento\Bundle\Model\Option $option */
        foreach ($optionsCollection->getItems() as $option) {
            /** @var \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection */
            $selectionsCollection = $typeInstance->getSelectionsCollection(
                [$option->getId()],
                $product
            );

            $wrappedOption = new \M2E\TikTokShop\Model\Magento\Product\Bundle\OptionWrapper($option);
            /** @var \Magento\Catalog\Model\Product $selection */
            foreach ($selectionsCollection->getItems() as $selection) {
                $wrappedOption->addSelection(
                    new \M2E\TikTokShop\Model\Magento\Product\Bundle\SelectionWrapper(
                        $selection,
                        $wrappedOption
                    )
                );
            }

            $options[] = $wrappedOption;
        }

        return $options;
    }
}
