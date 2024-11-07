<?php

namespace M2E\TikTokShop\Block\Adminhtml\Traits;

trait BlockTrait
{
    public function __(...$args): string
    {
        return (string)__(...$args);
    }

    public function getTooltipHtml(string $content, $directionToRight = false): string
    {
        $directionToRightClass = $directionToRight ? 'TikTokShop-field-tooltip-right' : '';

        return <<<HTML
<div class="TikTokShop-field-tooltip admin__field-tooltip {$directionToRightClass}">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content">
        {$content}
    </div>
</div>
HTML;
    }

    public function appendHelpBlock($data)
    {
        return $this->getLayout()->addBlock(\M2E\TikTokShop\Block\Adminhtml\HelpBlock::class, '', 'main.top')
                    ->setData(
                        $data,
                    );
    }

    /**
     * @param string $block
     * @param string $name
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setPageActionsBlock(string $block, string $name = '')
    {
        return $this->getLayout()->addBlock($block, $name, 'page.main.actions');
    }
}
