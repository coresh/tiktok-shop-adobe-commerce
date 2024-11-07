<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Grid;

use Magento\Backend\Block\Widget\Grid\Container;
use M2E\TikTokShop\Block\Adminhtml\Traits;

abstract class AbstractContainer extends Container
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'M2E_TikTokShop';
    }
}
