<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Magento\Button
 */
class Button extends \Magento\Backend\Block\Widget\Button
{
    protected $helperFactory;

    //########################################

    public function __construct(
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->helperFactory = $helperFactory;

        parent::__construct($context, $data);
    }

    //########################################
}
