<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Column\Filter\AttributesOptions
 */
class AttributesOptions extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    //########################################

    public function getHtml()
    {
        $id = $this->getColumn()->getId();
        $html = '<div id="attributes-options-filter_' . $id . '" class="attributes-options-filter">' .
            '<div class="attributes-options-filter-selector">' .
            '<select name="' . $this->_getHtmlName() . '" id="' . $this->_getHtmlId() .
            '" class="no-changes admin__control-select">';

        foreach ($this->_getOptions() as $option) {
            $html .= $this->_renderOption($option, null);
        }

        $html .= '</select>' . $this->getRemoveOptionButtonHtml() . '</div>' .
            '<div class="attributes-options-filter-values">';

        $values = $this->getValue();
        if (is_array($values)) {
            $i = 0;
            foreach ($values as $option) {
                if (is_array($option) && isset($option['value'])) {
                    $i++;
                    $html .= $this->renderAttrValue($i, $option);
                }
            }
        }

        $html .= '</div>' .
            '</div>';

        return $html;
    }

    protected function _renderOption($option, $value)
    {
        $selected = (($option['label'] == $value && ($value !== null)) ? ' selected="selected"' : '');

        return '<option value="' . $this->escapeHtml($option['label']) . '"' . $selected . '>' .
            $this->escapeHtml($option['label']) . '</option>';
    }

    protected function renderAttrValue($key, $option)
    {
        return '<div>
            <div>' . $option['attr'] . ' </div>
            <input style="width: 85%;" type="text" name="' . $this->getColumn()->getId() .
            '[' . $key . '][value]" value="' . $this->escapeHtml($option['value']) . '">
            <input type="hidden" name="' . $this->getColumn()->getId() .
            '[' . $key . '][attr]" value="' . $this->escapeHtml($option['attr']) . '">' .
            $this->getRemoveOptionButtonHtml() .
            '</div>';
    }

    protected function getRemoveOptionButtonHtml()
    {
        $src = $this->getViewFileUrl('M2E_TikTokShop::images/rule_component_remove.gif');
        $html = '<img src="' . $src . '" class="filter-param-remove v-middle" alt="" style="display: none;"
                                         title="' . __('Remove') . '"/>';

        return $html;
    }

    public function getCondition()
    {
        $values = $this->getValue();
        $conditions = [];
        foreach ($values as $value) {
            $conditions[] = [
                'regexp' => '"variation_product_options":[^}]*' .
                    $value['attr'] . '[[:space:]]*":"[[:space:]]*' . $value['value'] . '[[:space:]]*',
            ];
        }

        return $conditions;
    }

    //########################################
}
