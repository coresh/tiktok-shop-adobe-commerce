<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Category;

use M2E\TikTokShop\Block\Adminhtml\Traits;

abstract class AbstractCategory extends \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $blockContext,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->css = $blockContext->getCss();
        $this->jsPhp = $blockContext->getJsPhp();
        $this->js = $blockContext->getJs();
        $this->jsTranslator = $blockContext->getJsTranslator();
        $this->jsUrl = $blockContext->getJsUrl();

        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $data);
    }
}
