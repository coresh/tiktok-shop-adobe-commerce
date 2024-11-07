<?php

namespace M2E\TikTokShop\Block\Adminhtml\Tag;

class SwitcherFactory
{
    public function create(
        \Magento\Framework\View\LayoutInterface $layout,
        string $label,
        string $controllerName
    ): Switcher {
        return $layout->createBlock(Switcher::class, 'tag_switcher', [
            'label' => $label,
            'controllerName' => $controllerName,
        ]);
    }
}
