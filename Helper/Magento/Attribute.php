<?php

namespace M2E\TikTokShop\Helper\Magento;

use M2E\Core\Helper\Magento\AbstractHelper;

class Attribute extends AbstractHelper
{
    private \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration;
    private \M2E\TikTokShop\Model\Currency $currency;
    private \M2E\Core\Helper\Magento\Attribute $coreAttributeHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Currency $currency,
        \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Core\Helper\Magento\Attribute $coreAttributeHelper
    ) {
        parent::__construct($objectManager);
        $this->currency = $currency;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->coreAttributeHelper = $coreAttributeHelper;
    }

    public function convertAttributeTypePriceFromStoreToShop(
        \M2E\TikTokShop\Model\Magento\Product $magentoProduct,
        $attributeCode,
        string $currencyCode,
        int $store
    ) {
        $attributeValue = $magentoProduct->getAttributeValue($attributeCode);
        if (empty($attributeValue)) {
            return $attributeValue;
        }

        $isPriceConvertEnabled = $this->moduleConfiguration->isEnableMagentoAttributePriceTypeConvertingMode();

        if ($isPriceConvertEnabled && $this->coreAttributeHelper->isAttributeInputTypePrice($attributeCode)) {
            $attributeValue = $this->currency->convertPrice(
                $attributeValue,
                $currencyCode,
                $store
            );
        }

        return $attributeValue;
    }
}
