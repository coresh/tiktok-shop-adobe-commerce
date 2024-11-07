<?php

namespace M2E\TikTokShop\Block\Adminhtml\System\Config\Form\Element;

use M2E\TikTokShop\Block\Adminhtml\Traits\BlockTrait;

trait AbstractElementTrait
{
    use BlockTrait;

    /**
     * @param string $idSuffix
     * @param string $scopeLabel
     *
     * @return string
     */
    public function getLabelHtml($idSuffix = '', $scopeLabel = ''): string
    {
        $scopeLabel = $scopeLabel ? ' data-config-scope="' . $scopeLabel . '"' : '';

        if ($this->getLabel() !== null) {
            $html = '<label class="label admin__field-label" for="' .
                $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId(
                    'label'
                ) . ' style="width: 35%"><span' . $scopeLabel . '>' . $this->_escape(
                    $this->getLabel()
                ) . '</span></label>' . "\n";
        } else {
            $html = '';
        }

        return $html;
    }

    /**
     * Serialize attributes
     *
     * @param array $attributes
     * @param string $valueSeparator
     * @param string $fieldSeparator
     * @param string $quote
     *
     * @return string
     */
    public function serialize($attributes = [], $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"'): string
    {
        $data = [];
        foreach ($attributes as $key) {
            $value = $this->getData($key);
            if ($value !== null) {
                $data[] = $key . $valueSeparator . $quote . $value . $quote;
            }
        }

        return implode($fieldSeparator, $data);
    }
}
