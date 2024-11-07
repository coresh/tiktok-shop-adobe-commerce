<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Renderer;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Magento\Renderer\CssRenderer
 */
class CssRenderer extends AbstractRenderer
{
    protected $css = [];
    protected $cssFiles = [];

    public function add($css)
    {
        $this->css[] = $css;

        return $this;
    }

    public function addFile($file)
    {
        $this->cssFiles[] = $file;

        return $this;
    }

    public function getFiles()
    {
        return $this->cssFiles;
    }

    public function render()
    {
        return implode($this->css);
    }
}
