<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\Category;

class Edit extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Template\AbstractCategory
{
    public function execute()
    {
        $selectedValue = $this->getRequest()->getParam('selected_value');
        $selectedPath = $this->getRequest()->getParam('selected_path');

        /** @var \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Edit $editBlock */
        $editBlock = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Edit::class,
        );

        if (!empty($selectedValue)) {
            $editBlock->setSelectedCategory($selectedValue, $selectedPath);
        }

        $html = $editBlock->toHtml();
        $this->setAjaxContent($html);

        return $this->getResult();
    }
}
