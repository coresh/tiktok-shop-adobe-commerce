<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

use M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Edit;

class GetChooserEditHtml extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    public function execute()
    {
        $selectedValue = $this->getRequest()->getParam('selected_value');
        $selectedPath = $this->getRequest()->getParam('selected_path');
        $viewMode = $this->getRequest()->getParam('view_mode', Edit::WITHOUT_TABS_VIEW_MODE);

        /** @var Edit $editBlock */
        $editBlock = $this->getLayout()->createBlock(Edit::class);
        $editBlock->setData(Edit::VIEW_MODE_KEY, $viewMode);

        if (
            !empty($selectedPath)
            && !empty($selectedValue)
        ) {
            $editBlock->setSelectedCategory($selectedValue, $selectedPath);
        }

        $this->setAjaxContent($editBlock->toHtml());

        return $this->getResult();
    }
}
