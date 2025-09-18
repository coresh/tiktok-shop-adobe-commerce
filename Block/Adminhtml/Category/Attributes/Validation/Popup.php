<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Category\Attributes\Validation;

class Popup extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    protected $_template = 'M2E_TikTokShop::category/attributes/validation_popup.phtml';

    public function getModalOpenUrl(): string
    {
        return $this->getUrl('*/tiktokshop_category_attribute_validation_modal/open');
    }
}
