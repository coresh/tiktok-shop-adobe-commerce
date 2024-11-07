<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento;

use Magento\Backend\Block\Widget;
use M2E\TikTokShop\Block\Adminhtml\Traits;

abstract class AbstractBlock extends Widget
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    public function __construct(\M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context, array $data = [])
    {
        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        parent::__construct($context, $data);
    }
}
