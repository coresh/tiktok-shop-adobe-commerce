<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Product;

abstract class Grid extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    public $hideMassactionColumn = false;
    protected $hideMassactionDropDown = false;

    protected $showAdvancedFilterProductsOption = true;
    protected $useAdvancedFilter = true;

    /** @var \M2E\TikTokShop\Helper\Data */
    protected $dataHelper;
    public ?string $isAjax;
    private \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper;
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;

    public function __construct(
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->sessionHelper = $sessionHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------

        //$this->isAjax = \M2E\TikTokShop\Helper\Json::encode($this->getRequest()->isXmlHttpRequest());
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->css->addFile('magento/product/grid.css');

        return parent::_prepareLayout();
    }

    //########################################

    /**
     * @inheritdoc
     */
    public function setCollection($collection)
    {
        if ($collection->getStoreId() === null) {
            $collection->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        }

        /** @var \M2E\TikTokShop\Model\Magento\Product\Rule $ruleModel */
        $ruleModel = $this->globalDataHelper->getValue('rule_model');

        if ($ruleModel !== null && $this->useAdvancedFilter) {
            $ruleModel->setAttributesFilterToCollection($collection);
        }

        parent::setCollection($collection);
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // Set fake action
        // ---------------------------------------
        if ($this->getMassactionBlock()->getCount() == 0) {
            $this->getMassactionBlock()->addItem('fake', [
                'label' => '&nbsp;&nbsp;&nbsp;&nbsp;',
                'url' => '#',
            ]);
            // Header of grid with massactions is rendering in other way, than with no massaction
            // so it causes broken layout when the actions are absent
            $this->css->add(
                <<<CSS
            #{$this->getId()} .admin__data-grid-header {
                display: -webkit-flex;
                display: flex;
                -webkit-flex-wrap: wrap;
                flex-wrap: wrap;
            }

            #{$this->getId()} > .admin__data-grid-header > .admin__data-grid-header-row:first-child {
                width: 38%;
                margin-top: 1.1em;
            }
            #{$this->getId()} > .admin__data-grid-header > .admin__data-grid-header-row:last-child {
                width: 62%;
            }
CSS
            );
        }

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _prepareMassactionColumn()
    {
        if ($this->hideMassactionColumn) {
            return;
        }
        parent::_prepareMassactionColumn();
    }

    public function getMassactionBlockHtml()
    {
        if (!$this->useAdvancedFilter) {
            return $this->hideMassactionColumn ? '' : parent::getMassactionBlockHtml();
        }

        $advancedFilterBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Listing\Product\Rule::class);
        $advancedFilterBlock->setShowHideProductsOption($this->showAdvancedFilterProductsOption);
        $advancedFilterBlock->setGridJsObjectName($this->getJsObjectName());

        return $advancedFilterBlock->toHtml() . (($this->hideMassactionColumn)
                ? '' : parent::getMassactionBlockHtml());
    }

    //########################################

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        return \M2E\TikTokShop\Helper\Data::escapeHtml($value);
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ($row->getData('is_in_stock') === null) {
            return __('N/A');
        }

        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">' . __('Out of Stock') . '</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $rowVal = $row->getData();

        if (!isset($rowVal['price']) || (float)$rowVal['price'] <= 0) {
            $value = 0;
            $value = '<span style="color: red;">' . $value . '</span>';
        }

        return $value;
    }

    public function callbackColumnQty($value, $row, $column, $isExport)
    {
        if ($value <= 0) {
            $value = 0;
            $value = '<span style="color: red;">' . $value . '</span>';
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
            $value = '<span style="color: red;">' . $value . '</span>';
        }

        return $value;
    }

    //########################################

    public function getRowUrl($item)
    {
        return false;
    }

    //########################################

    public function getStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    //########################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!$this->getChild('advanced_filter_button')) {
            $buttonSettings = [
                'class' => 'task action-default scalable action-secondary',
                'id' => 'advanced_filter_button',
            ];

            if (!$this->isShowRuleBlock()) {
                $buttonSettings['label'] = __('Show Advanced Filter');
                $buttonSettings['onclick'] = 'ProductGridObj.advancedFilterToggle()';
            } else {
                $buttonSettings['label'] = __('Advanced Filter');
                $buttonSettings['onclick'] = '';
                $buttonSettings['class'] = $buttonSettings['class']
                    . ' advanced-filter-button-active';
            }

            $buttonBlock = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class);
            $buttonBlock->setData($buttonSettings);
            $this->setChild('advanced_filter_button', $buttonBlock);
        }

        return $this->getChildHtml('advanced_filter_button');
    }

    public function getMainButtonsHtml()
    {
        $html = '';

        if ($this->getFilterVisibility()) {
            $html .= $this->getSearchButtonHtml();

            if ($this->useAdvancedFilter) {
                $html .= $this->getAdvancedFilterButtonHtml();
            }

            $html .= $this->getResetFilterButtonHtml();
        }

        return $html;
    }

    //########################################

    protected function _toHtml()
    {
        // ---------------------------------------

        if ($this->hideMassactionDropDown) {
            $this->css->add(
                <<<CSS
    #{$this->getHtmlId()}_massaction .admin__grid-massaction-form {
        display: none;
    }
    #{$this->getHtmlId()}_massaction .mass-select-wrap {
        margin-left: -1.3em;
    }
CSS
            );
        }
        // ---------------------------------------

        // ---------------------------------------
        $isShowRuleBlock = \M2E\TikTokShop\Helper\Json::encode($this->isShowRuleBlock());

        $this->js->add(
            <<<JS
        jQuery(function()
        {
            if ({$isShowRuleBlock}) {
                jQuery('#listing_product_rules').show();
                jQuery('#{$this->getId()} .admin__data-grid-header-row:last-child')
                .css('width', '100%');

                if ($('advanced_filter_button')) {
                    $('advanced_filter_button').simulate('click');
                }
            }
               $$('#listing_product_rules select.element-value-changer option').each(function(el) {
                if ((el.value == '??' && el.selected) || (el.value == '!??' && el.selected)) {
                    setTimeout(function () {
                        $(el.parentElement.parentElement.parentElement.nextElementSibling).hide();
                    }, 10);
                }
            });
            $$('#listing_product_rules')
                .invoke('observe', 'change', function (event) {
                    let target = event.target;
                    if (target.value == '??' || target.value == '!??') {
                        setTimeout(function () {
                            $(target.parentElement.parentElement.nextElementSibling).hide();
                        }, 10);
                    }
                });
        });
JS
        );
        // ---------------------------------------

        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::_toHtml();
        }

        // ---------------------------------------
        $this->jsTranslator->addTranslations([
            'Please select the Products you want to perform the Action on.' => \M2E\TikTokShop\Helper\Data::escapeJs(
                __('Please select the Products you want to perform the Action on.')
            ),
            'Show Advanced Filter' => __('Show Advanced Filter'),
            'Hide Advanced Filter' => __('Hide Advanced Filter'),
        ]);

        // ---------------------------------------

        $isMassActionExists = (int)($this->getMassactionBlock()->getCount() > 1);

        $this->js->add(
            <<<JS
    require([
        'jquery',
        'TikTokShop/Magento/Product/Grid'
    ], function(jQuery){

        window.ProductGridObj = new MagentoProductGrid();
        ProductGridObj.setGridId('{$this->getJsObjectName()}');
        ProductGridObj.isMassActionExists = {$isMassActionExists};

        jQuery(function ()
        {
            {$this->getJsObjectName()}.doFilter = ProductGridObj.setFilter;
            {$this->getJsObjectName()}.resetFilter = ProductGridObj.resetFilter;
        });
    });
JS
        );

        return parent::_toHtml();
    }

    //########################################

    protected function isShowRuleBlock()
    {
        if (!$this->useAdvancedFilter) {
            return false;
        }

        $ruleData = $this->sessionHelper->getValue(
            $this->globalDataHelper->getValue('rule_prefix')
        );

        $showHideProductsOption = $this->sessionHelper->getValue(
            $this->globalDataHelper->getValue('hide_products_others_listings_prefix')
        );

        $showHideProductsOption === null && $showHideProductsOption = 1;

        return !empty($ruleData) || ($this->showAdvancedFilterProductsOption && $showHideProductsOption);
    }

    //########################################
}
