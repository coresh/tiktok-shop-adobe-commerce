<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Custom\Magento;

use M2E\TikTokShop\Model\Magento\Product\Rule\Condition\AbstractModel;

class Stock extends \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'magento_stock';

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
        return (string)__('Stock Availability');
    }

    /**
     * - MSI engine v. 2.3.2: Index tables have correct salable status
     * - Regular engine: Index table has status with no applied "Manage Stock" setting
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return int
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product): int
    {
        $magentoProduct = $this->magentoProductFactory->create();
        $magentoProduct->setProduct($product);

        return $this->magentoHelper->isMSISupportingVersion()
            ? (int)$magentoProduct->isStockAvailability()
            : (int)$magentoProduct->getStockItem()->getDataByKey('is_in_stock');
    }

    public function getInputType(): string
    {
        return AbstractModel::INPUT_TYPE_SELECT;
    }

    public function getValueElementType(): string
    {
        return AbstractModel::VALUE_ELEMENT_TYPE_SELECT;
    }

    public function getOptions(): array
    {
        return [
            [
                'value' => 1,
                'label' => (string)\__('In Stock'),
            ],
            [
                'value' => 0,
                'label' => (string)\__('Out Of Stock'),
            ],
        ];
    }
}
