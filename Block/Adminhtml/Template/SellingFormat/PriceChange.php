<?php

namespace M2E\TikTokShop\Block\Adminhtml\Template\SellingFormat;

class PriceChange extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    /** @var string */
    protected $_template = 'template/selling_format/price_change.phtml';

    /** @var \M2E\TikTokShop\Helper\Magento\Attribute */
    public $magentoAttributeHelper;
    /** @var array */
    private $allAttributes;
    /** @var array */
    private $attributesByInputTypes;

    /**
     * @param \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper
     * @param \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context
     * @param array $data
     */
    public function __construct(
        \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->magentoAttributeHelper = $magentoAttributeHelper;
        $this->allAttributes = $magentoAttributeHelper->getAll();
        $this->attributesByInputTypes = [
            'text_price' => $magentoAttributeHelper->filterByInputTypes($this->allAttributes, ['text', 'price']),
        ];
    }

    /**
     * @param string $priceModifierString
     *
     * @return array
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getPriceModifierAttributes(string $priceModifierString): array
    {
        $priceModifier = \M2E\TikTokShop\Helper\Json::decode($priceModifierString);
        if (!is_array($priceModifier) || empty($priceModifier)) {
            return [];
        }

        $result = [];
        foreach ($priceModifier as $modification) {
            if (
                $modification['mode'] == \M2E\TikTokShop\Model\Template\SellingFormat::PRICE_MODIFIER_ATTRIBUTE
                && $modification['attribute_code']
            ) {
                $result[] = $modification['attribute_code'];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllAttributes(): array
    {
        return $this->allAttributes;
    }

    /**
     * @return array
     */
    public function getAttributesByInputTypes(): array
    {
        return $this->attributesByInputTypes;
    }

    /**
     * @return string|null
     */
    public function getPriceType(): ?string
    {
        return $this->getData('price_type');
    }

    /**
     * @return string
     */
    public function getPriceModifier(): string
    {
        return (string)$this->getData('price_modifier');
    }
}
