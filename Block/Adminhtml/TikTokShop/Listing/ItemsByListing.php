<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing;

use M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer;

class ItemsByListing extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('tiktokshopListingItemsByListing');
        $this->_controller = 'adminhtml_tikTokShop_listing_itemsByListing';
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $url = $this->getUrl('*/tiktokshop_listing_create/index', ['step' => 1, 'clear' => 1]);
        $this->addButton('add', [
            'label' => __('Add Listing'),
            'onclick' => 'setLocation(\'' . $url . '\')',
            'class' => 'action-primary',
            'button_class' => '',
        ]);

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(Tabs::class);
        $tabsBlock->activateItemsByListingTab();
        $tabsBlockHtml = $tabsBlock->toHtml();

        return $tabsBlockHtml . parent::_toHtml();
    }
}
