<?php

namespace M2E\TikTokShop\Model\TikTokShop\Magento\Product\Rule\Condition;

use M2E\TikTokShop\Model\Magento\Product\Rule\Custom\TikTokShop as TtsCustomFilters;

class Combine extends \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\Combine
{
    public function __construct(
        \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\ProductFactory $ruleConditionProductFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        parent::__construct($ruleConditionProductFactory, $objectManager, $context, $data);

        $this->setType(self::class);
    }

    protected function getConditionCombine(): string
    {
        return $this->getType() . '|tts|';
    }

    protected function getCustomLabel(): string
    {
        return (string)__(
            '%extension_title Values',
            [
                'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
            ]
        );
    }

    protected function getCustomOptions(): array
    {
        $attributes = $this->getCustomOptionsAttributes();

        if (empty($attributes)) {
            return [];
        }

        return $this->getOptions(
            \M2E\TikTokShop\Model\TikTokShop\Magento\Product\Rule\Condition\Product::class,
            $attributes,
            ['tts']
        );
    }

    protected function getCustomOptionsAttributes(): array
    {
        return [
            TtsCustomFilters\OnlineCategory::NICK => __('Category ID'),
            TtsCustomFilters\OnlineQty::NICK => __('Available QTY'),
            TtsCustomFilters\OnlineSku::NICK => __('SKU'),
            TtsCustomFilters\OnlineTitle::NICK => __('Title'),
            TtsCustomFilters\ProductId::NICK => __('Product ID'),
            TtsCustomFilters\Status::NICK => __('Status'),
            TtsCustomFilters\OnlinePrice::NICK => __('Price'),
        ];
    }
}
