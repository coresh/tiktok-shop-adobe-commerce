<?php

namespace M2E\TikTokShop\Model\TikTokShop\Magento\Product;

class Rule extends \M2E\TikTokShop\Model\Magento\Product\Rule
{
    private \M2E\TikTokShop\Model\TikTokShop\Magento\Product\Rule\Condition\CombineFactory $ttsRuleCombineFactory;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Magento\Product\Rule\Condition\CombineFactory $ttsRuleCombineFactory,
        \Magento\Framework\Data\Form $form,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
        \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\CombineFactory $ruleConditionCombineFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $form,
            $productFactory,
            $resourceIterator,
            $ruleConditionCombineFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->ttsRuleCombineFactory = $ttsRuleCombineFactory;
    }

    public function getConditionObj(): \M2E\TikTokShop\Model\TikTokShop\Magento\Product\Rule\Condition\Combine
    {
        return $this->ttsRuleCombineFactory->create();
    }
}
