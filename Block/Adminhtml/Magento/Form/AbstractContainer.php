<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Form;

use Magento\Backend\Block\Widget\Form\Container;
use M2E\TikTokShop\Block\Adminhtml\Traits;
use M2E\TikTokShop\Block\Adminhtml\Magento\Renderer;

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
