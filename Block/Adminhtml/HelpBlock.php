<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml;

class HelpBlock extends Magento\AbstractBlock
{
    protected $_template = 'M2E_TikTokShop::help_block.phtml';

    public function getId(): string
    {
        if (null === $this->getData('id') && $this->hasContent()) {
            $this->setData('id', 'block_notice_' . crc32($this->getContent()));
        }

        return (string)$this->getData('id');
    }

    public function hasContent(): bool
    {
        return !empty($this->getContent());
    }

    public function setContent(string $content): void
    {
        $this->setData('content', $content);
    }

    public function getContent(): string
    {
        return (string)$this->getData('content');
    }

    public function hasStyle(): bool
    {
        return !empty($this->getData('style'));
    }

    public function getStyle(): string
    {
        return (string)$this->getData('style');
    }

    public function hasAlwaysShow(): bool
    {
        return !empty($this->getData('always_show'));
    }

    public function hasTooltiped(): bool
    {
        return !empty($this->getData('tooltiped'));
    }

    public function setTooltiped(): void
    {
        $this->setData('tooltiped', true);
    }

    public function hasNoCollapse(): bool
    {
        return !empty($this->getData('no_collapse'));
    }

    public function setNoCollapse(): void
    {
        $this->setData('no_collapse', true);
    }

    public function hasNoHide(): bool
    {
        return !empty($this->getData('no_hide'));
    }

    public function setNoHide(): void
    {
        $this->setData('no_hide', true);
    }

    public function getClass(): string
    {
        return (string)$this->getData('class');
    }

    // ----------------------------------------

    protected function _toHtml(): string
    {
        if ($this->hasContent()) {
            return parent::_toHtml();
        }

        return '';
    }
}
