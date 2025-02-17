<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\View;

class ControlPanel
{
    private \Magento\Backend\Model\Url $backendUrlBuilder;

    public function __construct(
        \Magento\Backend\Model\Url $backendUrlBuilder
    ) {
        $this->backendUrlBuilder = $backendUrlBuilder;
    }

    public function getPageUrl(array $params = []): string
    {
        return $this->backendUrlBuilder->getUrl($this->getPageRoute(), $params);
    }

    public function getPageRoute(): string
    {
        return '*/controlPanel/index';
    }

    public function getPageOverviewTabUrl(array $params = []): string
    {
        return $this->getPageUrl(
            array_merge($params, ['tab' => \M2E\Core\Block\Adminhtml\ControlPanel\Tab\Overview::TAB_ID])
        );
    }

    public function getPageInspectionTabUrl(array $params = []): string
    {
        return $this->getPageUrl(
            array_merge($params, ['tab' => \M2E\Core\Block\Adminhtml\ControlPanel\Tab\Inspection::TAB_ID])
        );
    }

    public function getPageDatabaseTabUrl(array $params = []): string
    {
        return $this->getPageUrl(
            array_merge($params, ['tab' => \M2E\Core\Block\Adminhtml\ControlPanel\Tab\Database::TAB_ID])
        );
    }

    public function getPageModuleTabUrl(array $params = []): string
    {
        return $this->getPageUrl(
            array_merge($params, ['tab' => \M2E\Core\Block\Adminhtml\ControlPanel\Tab\ModuleTools::TAB_ID])
        );
    }
}
