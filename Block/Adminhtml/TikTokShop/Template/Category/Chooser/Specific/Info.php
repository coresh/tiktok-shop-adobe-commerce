<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Category\Chooser\Specific;

class Info extends \M2E\TikTokShop\Block\Adminhtml\Widget\Info
{
    protected function _prepareLayout()
    {
        $this->setInfo(
            [
                [
                    'label' => __('Category'),
                    'value' => $this->getData('path'),
                ],
            ]
        );

        return parent::_prepareLayout();
    }
}
