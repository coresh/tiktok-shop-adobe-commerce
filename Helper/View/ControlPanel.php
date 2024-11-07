<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\View;

class ControlPanel
{
    public const NICK = 'control_panel';

    public const TAB_OVERVIEW = 'overview';
    public const TAB_INSPECTION = 'inspection';
    public const TAB_DATABASE = 'database';
    public const TAB_TOOLS_MODULE = 'tools_module';
    public const TAB_CRON = 'cron';
    public const TAB_DEBUG = 'debug';

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

    public function getPageOwerviewTabUrl(array $params = []): string
    {
        return $this->getPageUrl(array_merge($params, ['tab' => self::TAB_OVERVIEW]));
    }

    public function getPageInspectionTabUrl(array $params = []): string
    {
        return $this->getPageUrl(array_merge($params, ['tab' => self::TAB_INSPECTION]));
    }

    public function getPageDatabaseTabUrl(array $params = []): string
    {
        return $this->getPageUrl(array_merge($params, ['tab' => self::TAB_DATABASE]));
    }

    public function getPageModuleTabUrl(array $params = []): string
    {
        return $this->getPageUrl(array_merge($params, ['tab' => self::TAB_TOOLS_MODULE]));
    }
}
