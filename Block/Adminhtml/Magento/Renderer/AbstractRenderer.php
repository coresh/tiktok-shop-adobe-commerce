<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Renderer;

/**
 * @deprecated
 */
abstract class AbstractRenderer
{
    protected $helperFactory;

    //########################################

    public function __construct(
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        $this->helperFactory = $helperFactory;
    }

    //########################################

    abstract public function render();

    //########################################
}
