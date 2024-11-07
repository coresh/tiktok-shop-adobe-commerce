<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento\Product\Rule\Custom\Magento;

use M2E\TikTokShop\Model\Magento\Product\Rule\Condition\AbstractModel;

class TypeId extends \M2E\TikTokShop\Model\Magento\Product\Rule\Custom\AbstractCustomFilter
{
    public const NICK = 'magento_type_id';

    private \Magento\Catalog\Model\Product\Type $type;
    private \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \Magento\Catalog\Model\Product\Type $type
    ) {
        $this->type = $type;
        $this->magentoProductHelper = $magentoProductHelper;
    }

    public function getLabel(): string
    {
        return (string)__('Product Type');
    }

    public function getValueByProductInstance(\Magento\Catalog\Model\Product $product)
    {
        return $product->getTypeId();
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
        $magentoProductTypes = $this->type->getOptionArray();
        $knownTypes = $this->magentoProductHelper->getOriginKnownTypes();

        $options = [];
        foreach ($magentoProductTypes as $type => $magentoProductTypeLabel) {
            if (!in_array($type, $knownTypes)) {
                continue;
            }

            $options[] = [
                'value' => $type,
                'label' => $magentoProductTypeLabel,
            ];
        }

        return $options;
    }
}
