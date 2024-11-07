<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Tabs;

class Recent extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('tikTokShopCategoryChooserCategoryRecent');
        $this->setTemplate('tiktokshop/template/category/chooser/tabs/recent.phtml');
    }
}
