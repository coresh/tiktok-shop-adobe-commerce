<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Button;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Magento\Button\MagentoAttribute
 */
class MagentoAttribute extends \M2E\TikTokShop\Block\Adminhtml\Magento\Button
{
    protected function _prepareAttributes($title, $classes, $disabled)
    {
        $destinationId = $this->getDestinationId();
        $onClickCallback = $this->getOnClickCallback() ? $this->getOnClickCallback() : false;

        $onclick = "AttributeObj.appendToText('selectAttr_{$destinationId}', '{$destinationId}');";

        if ($onClickCallback) {
            $onclick .= "{$onClickCallback}";
        }

        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getElementName(),
            'title' => $title,
            'type' => $this->getType(),
            'class' => join(' ', $classes) . ' magento-attribute-btn',
            'onclick' => $onclick,
            'style' => $this->getStyle(),
            'value' => $this->getValue(),
            'disabled' => $disabled,
        ];

        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr)
                    ? $attr : \M2E\Core\Helper\Json::encode($attr);
            }
        }

        return $attributes;
    }

    //########################################
}
