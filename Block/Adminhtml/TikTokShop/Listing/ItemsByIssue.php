<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing;

use M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer;

class ItemsByIssue extends AbstractContainer
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ttsListingItemsByIssue');
        $this->_controller = 'adminhtml_tikTokShop_listing_itemsByIssue';
        // ---------------------------------------

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('add');
        $this->buttonList->remove('save');
        $this->buttonList->remove('edit');
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('switcher.css');

        return parent::_prepareLayout();
    }

    /**
     * @ingeritdoc
     */
    protected function _toHtml(): string
    {
        $filterBlockHtml = $this->getFilterBlockHtml();

        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Listing\Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock(Tabs::class);
        $tabsBlock->activateItemsByIssueTab();
        $tabsBlockHtml = $tabsBlock->toHtml();

        return $filterBlockHtml . $tabsBlockHtml . parent::_toHtml();
    }

    private function getFilterBlockHtml(): string
    {
        $shopSwitcherBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Shop\Switcher::class)
            ->setData([
                'controller_name' => $this->getRequest()->getControllerName(),
            ]);

        $accountSwitcherBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Account\Switcher::class)
            ->setData([
                'controller_name' => $this->getRequest()->getControllerName(),
            ]);

        return <<<HTML
<div class="page-main-actions">
    <div class="filter_block">
        {$accountSwitcherBlock->toHtml()}
        {$shopSwitcherBlock->toHtml()}
    </div>
</div>
HTML;
    }
}
