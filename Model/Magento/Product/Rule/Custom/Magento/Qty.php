<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Custom\Magento;

class Qty extends \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'magento_qty';

    private \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory;
    private \M2E\TikTokShop\Helper\Magento $magentoHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\TikTokShop\Helper\Magento $magentoHelper
    ) {
        $this->magentoProductFactory = $magentoProductFactory;
        $this->magentoHelper = $magentoHelper;
    }

    public function getLabel(): string
    {
        return (string)__('QTY');
    }

    /**
     * - MSI engine v. 2.3.2: Index tables have correct QTY for all product except Bundle
     * - Regular engine: Index table has 0 QTY for complex products (bundle, configurable, grouped)
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return float
     */
    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setProduct($product);

        if ($this->magentoHelper->isMSISupportingVersion()) {
            return $magentoProduct->isBundleType() ? 0 : $magentoProduct->getQty();
        }

        if (
            $magentoProduct->isBundleType() ||
            $magentoProduct->isConfigurableType() ||
            $magentoProduct->isGroupedType()
        ) {
            return 0;
        }

        return $magentoProduct->getQty();
    }
}
