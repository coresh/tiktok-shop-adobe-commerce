<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category\Attribute\Validation\Modal;

class Open extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    public function execute()
    {
        $templateCategoryId = (int)$this->getRequest()->getParam('template_category_id');

        if (empty($templateCategoryId)) {
            return $this->getResult();
        }

        $attributeValidationGrid = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Category\Attributes\Validation\Grid::class,
            '',
            [
                'templateCategoryId' => $templateCategoryId
            ]
        );

        $this->setAjaxContent($attributeValidationGrid);

        return $this->getResult();
    }
}
