<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing;

class Unmanaged extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    public function _construct(): void
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ttsListingUnmanaged');
        $this->_controller = 'adminhtml_tikTokShop_listing_unmanaged';
        // ---------------------------------------

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('add');
        $this->buttonList->remove('save');
        $this->buttonList->remove('edit');
    }

    protected function _toHtml()
    {
        $this->jsUrl->addUrls([
            'mapProductPopupHtml' => $this->getUrl(
                '*/listing_other_mapping/mapProductPopupHtml',
                [
                    'account_id' => $this->getRequest()->getParam('account'),
                ]
            ),
            'listing_other_mapping/map' => $this->getUrl('*/listing_other_mapping/map'),
            'mapAutoToProduct' => $this->getUrl('*/listing_other_mapping/autoMap'),
            'tiktokshop_listing/view' => $this->getUrl('*/tiktokshop_listing/view'),

            'prepareData' => $this->getUrl('*/listing_other_moving/prepareMoveToListing'),
            'moveToListingGridHtml' => $this->getUrl('*/listing_other_moving/moveToListingGrid'),
            'createListing' => $this->getUrl('*/listing_wizard/createUnmanaged'),
            'listingWizard' => $this->getUrl('*/listing_wizard/index'),

            'removingProducts' => $this->getUrl('*/tiktokshop_listing_unmanaged/removing'),
            'unmappingProducts' => $this->getUrl('*/listing_other_mapping/unmapping'),

        ]);

        $this->jsTranslator->addTranslations([
            'Link Item "%product_title%" with Magento Product' => __(
                'Link Item "%product_title%" with Magento Product'
            ),
            'Product(s) was Linked.' => __('Product(s) was Linked.'),
            'Add New Listing' => __('Add New Listing'),
            'failed_mapped' => __(
                'Some Items were not linked. Please edit <i>Product Linking Settings</i> under
            <i>Configuration > Account > Unmanaged Listings</i> or try to link manually.'
            ),
            'Product was Linked.' => __('Product was Linked.'),
            'Linking Product' => __('Linking Product'),
            'product_does_not_exist' => __('Product does not exist.'),
            'select_simple_product' => __(
                'Current TikTokShop version only supports Simple Products in Linking. Please, choose Simple Product.'
            ),
            'automap_progress_title' => __('Link Item(s) to Products'),
            'processing_data_message' => __('Processing %product_title% Product(s).'),
            'popup_title' => __('Moving TikTokShop Items'),
            'Not enough data' => __('Not enough data.'),
            'Product(s) was Unlinked.' => __('Product(s) was Unlinked.'),
            'Product(s) was Removed.' => __('Product(s) was Removed.'),
            'task_completed_message' => __('Task completed. Please wait ...'),
            'sending_data_message' => __('Sending %product_title% Product(s) data on TikTokShop.'),
            'listing_locked_message' => __('The Listing was locked by another process. Please try again later.'),
            'listing_empty_message' => __('Listing is empty.'),

            'select_items_message' => __('Please select the Products you want to perform the Action on.'),
            'select_action_message' => __('Please select Action.'),
        ]);

        $this->js->addRequireJs(
            [
                'jQuery' => 'jquery',

                'p' => 'TikTokShop/Plugin/ProgressBar',
                'a' => 'TikTokShop/Plugin/AreaWrapper',
                'lm' => 'TikTokShop/Listing/Moving',
                'lom' => 'TikTokShop/Listing/Product/Unmanaged/Mapping',
                'loa' => 'TikTokShop/Listing/Other/AutoMapping',
                'lor' => 'TikTokShop/Listing/Other/Removing',
                'lou' => 'TikTokShop/Listing/Other/Unmapping',

                'elog' => 'TikTokShop/TikTokShop/Listing/Other/Grid',
            ],
            <<<JS

        TikTokShop.customData.gridId = 'ttsListingOtherGrid';

        window.TiktokshopListingOtherGridObj = new TikTokShopListingOtherGrid('ttsListingUnmanagedGrid');
        window.ListingOtherMappingObj = new ListingProductUnmanagedMapping(TiktokshopListingOtherGridObj);

        TiktokshopListingOtherGridObj.movingHandler.setProgressBar('listing_other_progress_bar');
        TiktokshopListingOtherGridObj.movingHandler.setGridWrapper('listing_other_content_container');

        TiktokshopListingOtherGridObj.autoMappingHandler.setProgressBar('listing_other_progress_bar');
        TiktokshopListingOtherGridObj.autoMappingHandler.setGridWrapper('listing_other_content_container');

        jQuery(function() {
            TiktokshopListingOtherGridObj.afterInitPage();
        });
JS
        );

        return '<div id="listing_other_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_other_content_container">' .
            parent::_toHtml() .
            '</div>';
    }
}
