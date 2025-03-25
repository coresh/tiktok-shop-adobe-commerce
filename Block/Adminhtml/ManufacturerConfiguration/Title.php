<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration;

class Title extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    public function toHtml(): string
    {
        $css = <<<CSS
h2.gpsr-title {
    color: #303030;
    font-size: 1.7rem;
    font-weight: 600;
    padding: 7px 0 10px;
    border-bottom: 1px solid #cac3b4;
}
CSS;

        $this->css->add($css);

        return sprintf(
            '<h2 class="gpsr-title">%s</h2>',
            __('Manufacturer and Responsible Person Setting')
        );
    }
}
