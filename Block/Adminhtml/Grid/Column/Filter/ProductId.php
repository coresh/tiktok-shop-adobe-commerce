<?php

namespace M2E\TikTokShop\Block\Adminhtml\Grid\Column\Filter;

class ProductId extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\Range
{
    public function getHtml()
    {
        $anySelected = $noSelected = $yesSelected = '';
        $filterValue = (string)$this->getValue('is_mapped');

        $filterValue === '' && $anySelected = ' selected="selected" ';
        $filterValue === '0' && $noSelected = ' selected="selected" ';
        $filterValue === '1' && $yesSelected = ' selected="selected" ';

        $isEnabled = 1;
        $isDisabled = 0;

        $linkedText = __('Linked');
        $anyText = __('Any');
        $yesText = __('Yes');
        $noText = __('No');
        $html = <<<HTML
<div class="range" style="width: 145px;">
    <div class="range-line" style="width: auto;">
        <span class="label" style="width: auto;">
            $linkedText:&nbsp;
        </span>
        <select id="{$this->_getHtmlName()}"
                style="margin-left:6px; margin-top:5px; float:none; width:auto !important;"
                name="{$this->_getHtmlName()}[is_mapped]"
            >
            <option $anySelected value="">$anyText</option>
            <option $yesSelected value="$isEnabled">$yesText</option>
            <option $noSelected  value="$isDisabled">$noText</option>
        </select>
    </div>
</div>
HTML;

        return parent::getHtml() . $html;
    }

    public function getValue($index = null)
    {
        if ($index) {
            return $this->getData('value', $index);
        }

        $value = $this->getData('value');
        if (
            (isset($value['from']) && strlen($value['from']) > 0) ||
            (isset($value['to']) && strlen($value['to']) > 0) ||
            (isset($value['is_mapped']) && $value['is_mapped'] !== '')
        ) {
            return $value;
        }

        return null;
    }
}
