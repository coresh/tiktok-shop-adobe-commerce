<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order\Item\Product\Options;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class Mapping extends AbstractContainer
{
    protected $_template = 'order/item/product/options/mapping.phtml';

    private ?\M2E\TikTokShop\Model\Magento\Product $magentoProduct = null;
    /** @var \M2E\TikTokShop\Model\Order\Item[] */
    private array $orderItems;
    private \M2E\TikTokShop\Model\Magento\Product\BundleService $bundleService;
    private \Magento\Framework\Data\Form\Element\Factory $elementFactory;
    private \Magento\Framework\Data\FormFactory $formFactory;

    public function __construct(
        array $orderItems,
        \M2E\TikTokShop\Model\Magento\Product\BundleService $bundleService,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->orderItems = $orderItems;
        parent::__construct($context, $data);
        $this->bundleService = $bundleService;
        $this->elementFactory = $elementFactory;
        $this->formFactory = $formFactory;
    }

    public function getOrderItem(): \M2E\TikTokShop\Model\Order\Item
    {
        return reset($this->orderItems);
    }

    public function getOrderItemsIdsAsString(): string
    {
        $result = [];
        foreach ($this->orderItems as $orderItem) {
            $result[] = $orderItem->getId();
        }

        return implode(',', $result);
    }

    public function getProductTypeHeader(): string
    {
        $title = __('Custom Options');

        if ($this->magentoProduct->isBundleType()) {
            $title = __('Bundle Items');
        } elseif (
            $this->magentoProduct->isGroupedType() ||
            $this->magentoProduct->isConfigurableType()
        ) {
            $title = __('Associated Products');
        }

        return (string)$title;
    }

    public function isMagentoOptionSelected(array $magentoOption, array $magentoOptionValue): bool
    {
        if ($this->magentoProduct->isGroupedType()) {
            $associatedProducts = $this->getOrderItem()->getAssociatedProducts();

            if (
                count($associatedProducts) == 1
                && count(array_diff($associatedProducts, $magentoOptionValue['product_ids'])) == 0
            ) {
                return true;
            }

            return false;
        }

        $associatedOptions = $this->getOrderItem()->getAssociatedOptions();

        if (!isset($associatedOptions[(int)$magentoOption['option_id']])) {
            return false;
        }

        if (is_array($associatedOptions[(int)$magentoOption['option_id']])) {
            return in_array(
                $magentoOptionValue['value_id'],
                $associatedOptions[(int)$magentoOption['option_id']]
            );
        }

        return $associatedOptions[(int)$magentoOption['option_id']] == $magentoOptionValue['value_id'];
    }

    public function getChanelOption(): array
    {
        $combinedListingSkus = $this->getOrderItem()->getCombinedListingSkus();
        if (empty($combinedListingSkus)) {
            return [];
        }

        $result = [];
        foreach ($combinedListingSkus->getList() as $combinedListingSku) {
            $result[] = [
                'label' => $combinedListingSku->sellerSku,
                'value' => $combinedListingSku->productId,
            ];
        }

        return $result;
    }

    public function getMagentoOptions(): array
    {
        if ($this->magentoProduct->isBundleType()) {
            $magentoOptions = $this->bundleService->getOptionsWithSelections($this->magentoProduct);

            $result = [];
            foreach ($magentoOptions as $magentoOption) {
                $option = [
                    'option_id' => $magentoOption->getOptionId(),
                    'label' => $magentoOption->getLabel(),
                    'is_multiselect' => $magentoOption->isMultiselect(),
                ];

                foreach ($magentoOption->getSelections() as $selection) {
                    $option['values'][] = [
                        'value_id' => $selection->getSelectionId(),
                        'product_ids' => [$selection->getProductId()],
                        'label' => sprintf('%s (%s)', $selection->getLabel(), $selection->getSku()),
                    ];
                }

                $result[] = $option;
            }

            return $result;
        }

        return [];
    }

    public function renderOptionElement(array $option)
    {
        $label = sprintf(
            '<dt><label><span style="color: #eb5202">*</span>&nbsp;%s</label></dt>',
            $this->_escaper->escapeHtml($option['label'])
        );

        $select = '<dd style="border-bottom: 1px solid #E7E7E7; margin: 5px 0 15px; padding: 0 0 12px;"><div class="input-box">';
        if ($option['is_multiselect']) {
            $select .= $this->renderMultiselect($option);
        } else {
            $select .= $this->renderSelect($option);
        }
        $select .= '</div></dd>';

        return $label . $select;
    }

    protected function _beforeToHtml()
    {
        $this->magentoProduct = $this->getOrderItem()->getMagentoProduct();

        $magentoOptions = [];
        $magentoVariations = $this->magentoProduct->getVariationInstance()->getVariationsTypeRaw();

        if ($this->magentoProduct->isGroupedType()) {
            $magentoOptionLabel = __(
                \M2E\TikTokShop\Model\Magento\Product\Variation::GROUPED_PRODUCT_ATTRIBUTE_LABEL
            );

            $magentoOption = [
                'option_id' => 0,
                'label' => $magentoOptionLabel,
                'values' => [],
            ];

            foreach ($magentoVariations as $key => $magentoVariation) {
                $magentoOption['values'][] = [
                    'value_id' => $key,
                    'label' => sprintf(
                        '%s (%s)',
                        $magentoVariation->getName(),
                        $magentoVariation->getSku()
                    ),
                    'product_ids' => [$magentoVariation->getId()],
                ];
            }

            $magentoOptions[] = $magentoOption;
        } else {
            foreach ($magentoVariations as $magentoVariation) {
                $magentoOptionLabel = array_shift($magentoVariation['labels']);
                if ($magentoOptionLabel === '' || $magentoOptionLabel === null) {
                    $magentoOptionLabel = __('N/A');
                }

                $magentoOption = [
                    'option_id' => $magentoVariation['option_id'],
                    'label' => $magentoOptionLabel,
                    'values' => [],
                ];

                foreach ($magentoVariation['values'] as $magentoOptionValue) {
                    $magentoValueLabel = array_shift($magentoOptionValue['labels']);
                    if ($magentoValueLabel === '' || $magentoValueLabel === null) {
                        $magentoValueLabel = __('N/A');
                    }

                    $magentoOption['values'][] = [
                        'value_id' => $magentoOptionValue['value_id'],
                        'label' => $magentoValueLabel,
                        'product_ids' => $magentoOptionValue['product_ids'],
                    ];
                }

                $magentoOptions[] = $magentoOption;
            }
        }

        $this->setData('magento_options', $magentoOptions);
        // ---------------------------------------

        $this->setChild(
            'product_mapping_options_help_block',
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class)->setData([
                'content' => __(
                    'As %extension_title was not able to find appropriate Option in Magento Product ' .
                    'you are supposed find and Link it manualy.',
                    [
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ])
        );

        $this->setChild(
            'product_mapping_options_out_of_stock_message',
            $this->getLayout()->createBlock(\Magento\Framework\View\Element\Messages::class)
                 ->addWarning(__('Selected Product Option is Out of Stock.'))
        );

        parent::_beforeToHtml();
    }

    private function renderMultiselect(array $option): string
    {
        $multiselect = sprintf(
            '<select multiple="multiple" name="%s" class="form-element select admin__control-multiselect required-entry">',
            sprintf('option_id[%s][]', $option['option_id'])
        );
        $multiselect .= $this->renderSelectOptions($option);
        $multiselect .= '</select>';

        return $multiselect;
    }

    private function renderSelect(array $option): string
    {
        $selectHtml = sprintf(
            '<select name="%s" class="form-element select admin__control-select required-entry">',
            sprintf('option_id[%s]', $option['option_id'])
        );
        $selectHtml .= $this->renderSelectOptions($option);
        $selectHtml .= '</select>';

        return $selectHtml;
    }

    private function renderSelectOptions(array $option): string
    {
        $selectOptions = [];
        $selectOptions[] = sprintf(
            '<option value="" class="empty">%s</option>',
            __('Select Option...')
        );
        foreach ($option['values'] as $value) {
            $optionValue = json_encode([
                'value_id' => $value['value_id'],
                'product_ids' => $value['product_ids'],
            ]);
            $optionLabel = $value['label'];

            $selectedAttribute = '';
            if ($this->isMagentoOptionSelected($option, $value)) {
                $selectedAttribute = ' selected="selected"';
            }

            $selectOptions[] = sprintf(
                '<option value="%s"%s>%s</option>',
                $this->_escaper->escapeHtmlAttr($optionValue),
                $selectedAttribute,
                $optionLabel
            );
        }

        return implode('', $selectOptions);
    }
}
