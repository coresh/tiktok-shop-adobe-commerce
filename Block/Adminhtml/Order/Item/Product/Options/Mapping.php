<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order\Item\Product\Options;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

class Mapping extends AbstractContainer
{
    protected $_template = 'order/item/product/options/mapping.phtml';

    private ?\M2E\TikTokShop\Model\Magento\Product $magentoProduct = null;
    /** @var \M2E\TikTokShop\Model\Order\Item[] */
    private array $orderItems;

    public function __construct(
        array $orderItems,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->orderItems = $orderItems;
        parent::__construct($context, $data);
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

    public function getProductTypeHeader()
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

        if (
            isset($associatedOptions[(int)$magentoOption['option_id']])
            && $associatedOptions[(int)$magentoOption['option_id']] == $magentoOptionValue['value_id']
        ) {
            return true;
        }

        return false;
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
                    'label' => $magentoVariation->getName(),
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
                    'you are supposed find and Link it manualy.<br/>If you want to use the same Settings for the ' .
                    'similar subsequent Orders, select appropriate check-box at the bottom. <br/><br/>' .
                    '<b>Note:</b> Magento Order can be only created when all Products of Order are found ' .
                    'in Magento Catalog.',
                    [
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
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
}
