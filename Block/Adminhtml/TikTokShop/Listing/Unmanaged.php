<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing;

class Unmanaged extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    private \M2E\TikTokShop\Helper\Data $dataHelper;
    private \M2E\TikTokShop\Model\Listing\InventorySync\AccountLockManager $accountLockManager;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        \M2E\TikTokShop\Model\Listing\InventorySync\AccountLockManager $accountLockManager,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->accountRepository = $accountRepository;
        $this->accountLockManager = $accountLockManager;

        parent::__construct($context, $data);
    }

    public function _construct()
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

        $this->addResetBtn();
    }

    /**
     * @return void
     */
    private function addResetBtn(): void
    {
        $importIsInProgress = false;

        foreach ($this->accountRepository->getAll() as $account) {
            if ($this->accountLockManager->isExistByAccount($account)) {
                $importIsInProgress = true;
                break;
            }
        }

        $label = $importIsInProgress
            ? __('Products Import Is in Progress')
            : __('Reset Unmanaged Listings');
        $url = $this->getUrl('*/tiktokshop_listing_unmanaged/reset');

        $this->addButton(
            'reset_other_listings',
            [
                'label' => $label,
                'onclick' => "ListingOtherObj.showResetPopup('{$url}');",
                'class' => 'action-primary',
                'disabled' => $importIsInProgress,
            ]
        );
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('switcher.css');
        $this->setPageActionsBlock(\M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Unmanaged\PageActions::class);

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        $someProductsWereNotMappedMessage = __('Some Items were not linked. Please ' .
            'edit <i>Product Linking Settings</i> under <i>Configuration > Account > Unmanaged ' .
            'Listings</i> or try to link manually.');

        $this->jsUrl->addUrls($this->dataHelper->getControllerActions('Listing\Other'));
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
            'Product does not exist.' => __('Product does not exist.'),
            'Product(s) was Linked.' => __('Product(s) was Linked.'),
            'Add New Listing' => __('Add New Listing'),
            'failed_mapped' => $someProductsWereNotMappedMessage,
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
                'lom' => 'TikTokShop/Listing/Mapping',
                'loa' => 'TikTokShop/Listing/Other/AutoMapping',
                'lor' => 'TikTokShop/Listing/Other/Removing',
                'lou' => 'TikTokShop/Listing/Other/Unmapping',

                'elog' => 'TikTokShop/TikTokShop/Listing/Other/Grid',
            ],
            <<<JS

        TikTokShop.customData.gridId = 'ttsListingOtherGrid';

        window.TiktokshopListingOtherGridObj = new TikTokShopListingOtherGrid('ttsListingUnmanagedGrid');
        window.ListingOtherMappingObj = new ListingMapping(TiktokshopListingOtherGridObj);

        TiktokshopListingOtherGridObj.movingHandler.setProgressBar('listing_other_progress_bar');
        TiktokshopListingOtherGridObj.movingHandler.setGridWrapper('listing_other_content_container');

        TiktokshopListingOtherGridObj.autoMappingHandler.setProgressBar('listing_other_progress_bar');
        TiktokshopListingOtherGridObj.autoMappingHandler.setGridWrapper('listing_other_content_container');

        jQuery(function() {
            TiktokshopListingOtherGridObj.afterInitPage();
        });
JS
        );

        $this->js->add(
            <<<JS
    require(['TikTokShop/Listing/Other'], function(){

        window.ListingOtherObj = new ListingOther();

    });
JS
        );

        $progressBarHtml = '<div id="listing_other_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_other_content_container">' .
            parent::_toHtml() .
            '</div>';

        $tabsHtml = $this->getTabsBlockHtml();
        $resetPopupHtml = $this->getResetPopupHtml();

        return $tabsHtml . $progressBarHtml . $resetPopupHtml;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTabsBlockHtml(): string
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(Tabs::class);
        $tabsBlock->activateUnmanagedItemsTab();

        return $tabsBlock->toHtml();
    }

    /**
     * @return string
     */
    private function getResetPopupHtml(): string
    {
        $h3 = __('Confirm the Unmanaged Listings reset');
        $paragraphOne = __('This action will remove all the items from TikTokShop Unmanaged ' .
            'Listings. It will take some time to import them again.');
        $paragraphTwo = __('Do you want to reset the Unmanaged Listings?');

        return <<<HTML
<div style="display: none">
    <div id="reset_other_listings_popup_content" class="block_notices TikTokShop-box-style"
     style="display: none; margin-bottom: 0;">
        <div>
            <h3>$h3</h3>
            <p>$paragraphOne</p><br>
            <p>$paragraphTwo</p>
        </div>
    </div>
</div>
HTML;
    }
}
