<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Tabs;

abstract class AbstractHorizontalStaticTabs extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    /** @var array<string, array{content: string, url:string, title: string}> */
    private array $tabs = [];
    /** @var string[] */
    private array $registeredCss = [];
    private string $commonCssForTabsContainer = '';
    protected $_template = 'M2E_TikTokShop::magento/tabs/horizontal_static.phtml';

    abstract protected function init(): void;

    protected function _prepareLayout()
    {
        $this->init();

        return parent::_prepareLayout();
    }

    protected function addTab(
        string $tabId,
        string $content,
        string $url,
        ?string $title = null
    ): void {
        $this->tabs[$tabId] = [
            'content' => $content,
            'url' => $url,
            'title' => $title ?? $content,
        ];
    }

    /**
     * @return array<int, array{content: string, url:string, title: string, is_active: bool}>
     */
    public function getTabs(): array
    {
        $resultTabs = [];
        foreach ($this->tabs as $tabId => $val) {
            $resultTabs[] = array_merge(
                $val,
                ['is_active' => $this->isActiveTab($tabId)]
            );
        }

        return $resultTabs;
    }

    private function isActiveTab(string $tabId): bool
    {
        return $this->getActiveTab() === $tabId;
    }

    public function setActiveTabId(string $tabId): void
    {
        $this->setData('active_tab', $tabId);
    }

    private function getActiveTab(): string
    {
        return (string)$this->getData('active_tab');
    }

    protected function _toHtml()
    {
        $styles = $this->commonCssForTabsContainer;
        foreach ($this->registeredCss as $tabId => $tabCss) {
            if ($this->isActiveTab($tabId)) {
                $styles .= $tabCss;
                break;
            }
        }

        if (!empty($styles)) {
            $this->css->add('.TikTokShop-tabs-horizontal-static{ ' . $styles . ' }');
        }

        return parent::_toHtml();
    }

    protected function registerCssForTab(string $tabId, string $css): void
    {
        $this->registeredCss[$tabId] = $css;
    }

    protected function addCssForTabsContainer(string $css): void
    {
        $this->commonCssForTabsContainer = $css;
    }
}
