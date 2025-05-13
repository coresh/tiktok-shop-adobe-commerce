<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Mapping\Tabs;

class AttributeMapping extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected $_template = 'mapping/attribute_mapping.phtml';

    private \M2E\TikTokShop\Model\AttributeMapping\GeneralService $generalService;
    private \M2E\Core\Helper\Magento\Attribute $attributeHelper;

    public function __construct(
        \M2E\TikTokShop\Model\AttributeMapping\GeneralService $generalService,
        \M2E\Core\Helper\Magento\Attribute $attributeHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->generalService = $generalService;
        $this->attributeHelper = $attributeHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _beforeToHtml()
    {
        $this->jsUrl->add(
            $this->getUrl('*/mapping/save'),
            \M2E\TikTokShop\Block\Adminhtml\Mapping\Tabs::TAB_ID_MAPPING_ATTRIBUTES
        );

        return parent::_beforeToHtml();
    }

    /**
     * @return \M2E\Core\Model\AttributeMapping\Pair[]
     */
    public function getMappedAttributes(): array
    {
        return $this->generalService->getAll();
    }

    public function makeMagentoAttributesDropDownHtml(
        \M2E\Core\Model\AttributeMapping\Pair $attributeMapping
    ): string {
        $attributes = $this->attributeHelper->getAll();

        $html = sprintf(
            '<select id="attribute-%1$s" name="general_attributes[%1$s][magento_code]" class="%2$s">',
            $attributeMapping->getChannelAttributeCode(),
            'select admin__control-select'
        );
        $html .= sprintf('<option value="">%s</option>', __('None'));
        $html .= sprintf('<option value="">%s</option>', __('None'));
        $html .= sprintf(
            '<optgroup label="%s">',
            __('Magento Attributes')
        );
        foreach ($attributes as $attribute) {
            $html .= sprintf(
                '<option value="%1$s"%3$s>%2$s</option>',
                $attribute['code'],
                $attribute['label'],
                $attribute['code'] === $attributeMapping->getMagentoAttributeCode() ? ' selected' : ''
            );
        }
        $html .= '</optgroup>';
        $html .= '</select>';
        $html .= sprintf(
            "<input type='hidden' name='general_attributes[%s][title]' value='%s'>",
            $attributeMapping->getChannelAttributeCode(),
            $attributeMapping->getChannelAttributeTitle()
        );

        return $html;
    }
}
