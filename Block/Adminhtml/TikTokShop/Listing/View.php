<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing;

use M2E\TikTokShop\Block\Adminhtml\Log\AbstractGrid;

class View extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    private \M2E\TikTokShop\Helper\Url $urlHelper;
    private string $viewMode;
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\TikTokShop\Helper\Url $urlHelper,
        array $data = []
    ) {
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        /** @var \M2E\TikTokShop\Block\Adminhtml\Listing\View\Switcher $viewModeSwitcher */
        $viewModeSwitcher = $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Listing\View\Switcher::class);

        // Initialization block
        // ---------------------------------------
        $this->setId('tikTokShopListingView');
        $this->_controller = 'adminhtml_tikTokShop_listing_view_' . $viewModeSwitcher->getSelectedParam();
        $this->viewMode = $viewModeSwitcher->getSelectedParam();
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('add');
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $this->jsPhp->addConstants(
            [
                '\M2E\TikTokShop\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_PRODUCT_ID_FIELD' => AbstractGrid::LISTING_PRODUCT_ID_FIELD,
            ]
        );

        // ---------------------------------------
        $backUrl = $this->urlHelper->getBackUrl('*/tiktokshop_listing/index');

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $backUrl . '\');',
                'class' => 'back',
            ]
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/tiktokshop_log_listing_product',
            [
                \M2E\TikTokShop\Block\Adminhtml\Log\AbstractGrid::LISTING_ID_FIELD =>
                    $this->uiListingRuntimeStorage->getListing()->getId(),
            ]
        );
        $this->addButton(
            'view_log',
            [
                'label' => __('Logs & Events'),
                'onclick' => 'window.open(\'' . $url . '\',\'_blank\')',
            ]
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->addButton(
            'edit_templates',
            [
                'label' => __('Edit Settings'),
                'onclick' => '',
                'class' => 'drop_down edit_default_settings_drop_down primary',
                'class_name' => \M2E\TikTokShop\Block\Adminhtml\Magento\Button\DropDown::class,
                'options' => $this->getSettingsButtonDropDownItems(),
            ]
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl(
            '*/listing_wizard/create',
            [
                'listing_id' => $this->uiListingRuntimeStorage->getListing()->getId(),
                'type' => \M2E\TikTokShop\Model\Listing\Wizard::TYPE_GENERAL,
            ]
        );

        $this->addButton(
            'listing_product_wizard',
            [
                'id' => 'listing_product_wizard',
                'label' => __('Add Products'),
                'class' => 'add primary',
                'onclick' => "setLocation('$url')",
            ]
        );

        // ---------------------------------------

        $this->addGrid();

        return parent::_prepareLayout();
    }

    private function addGrid(): void
    {
        switch ($this->viewMode) {
            case \M2E\TikTokShop\Block\Adminhtml\Listing\View\Switcher::VIEW_MODE_TIKTOKSHOP:
                $gridClass = \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View\TikTokShop\Grid::class;
                break;
            case \M2E\TikTokShop\Block\Adminhtml\Listing\View\Switcher::VIEW_MODE_MAGENTO:
                $gridClass = \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View\Magento\Grid::class;
                break;
            case \M2E\TikTokShop\Block\Adminhtml\Listing\View\Switcher::VIEW_MODE_SETTINGS:
                $gridClass = \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\View\Settings\Grid::class;
                break;
            default:
                throw new \M2E\TikTokShop\Model\Exception\Logic(sprintf('Unknown view mode - %s', $this->viewMode));
        }

        $this->addChild('grid', $gridClass);
    }

    protected function _toHtml(): string
    {
        return '<div id="listing_view_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_view_content_container">' .
            parent::_toHtml() .
            '</div>';
    }

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        $this->jsUrl->add(
            $this->getUrl('*/tiktokshop_listing_variation_product_manage/index'),
            'variationProductManageOpenPopupUrl'
        );

        $this->jsTranslator->addTranslations(
            [
                'Remove Category' => __('Remove Category'),
                'Add New Rule' => __('Add New Rule'),
                'Add/Edit Categories Rule' => __('Add/Edit Categories Rule'),
                'Based on Magento Categories' => __('Based on Magento Categories'),
                'You must select at least 1 Category.' => __('You must select at least 1 Category.'),
                'Rule with the same Title already exists.' => __('Rule with the same Title already exists.'),
                'Compatibility Attribute' => __('Compatibility Attribute'),
                'Sell on Another Marketplace' => __('Sell on Another Shop'),
                'Create new' => __('Create new'),
                'Linking Product' => __('Linking Product'),
            ]
        );

        return parent::getGridHtml();
    }

    private function getSettingsButtonDropDownItems(): array
    {
        $items = [];

        $backUrl = $this->urlHelper->makeBackUrlParam(
            '*/tiktokshop_listing/view',
            ['id' => $this->uiListingRuntimeStorage->getListing()->getId()]
        );

        $url = $this->getUrl(
            '*/tiktokshop_listing/edit',
            [
                'id' => $this->uiListingRuntimeStorage->getListing()->getId(),
                'back' => $backUrl,
            ]
        );
        $items[] = [
            'label' => __('Configuration'),
            'onclick' => 'window.open(\'' . $url . '\',\'_blank\');',
            'default' => true,
        ];

        return $items;
    }
}
