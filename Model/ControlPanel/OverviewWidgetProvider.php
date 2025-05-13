<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel;

class OverviewWidgetProvider implements \M2E\Core\Model\ControlPanel\Overview\WidgetProviderInterface
{
    private \M2E\TikTokShop\Helper\Module\Maintenance $maintenance;
    private \M2E\TikTokShop\Model\Module\Environment $environment;
    /** @var \M2E\TikTokShop\Model\ControlPanel\Widget\CronInfo */
    private Widget\CronInfo $cronInfo;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Maintenance $maintenance,
        \M2E\TikTokShop\Model\Module\Environment $environment,
        \M2E\TikTokShop\Model\ControlPanel\Widget\CronInfo $cronInfo
    ) {
        $this->maintenance = $maintenance;
        $this->environment = $environment;
        $this->cronInfo = $cronInfo;
    }

    public function getExtensionModuleName(): string
    {
        return \M2E\TikTokShop\Model\ControlPanel\Extension::NAME;
    }

    /**
     * @return \M2E\Core\Model\ControlPanel\OverviewWidget[]
     */
    public function getWidgets(): array
    {
        return [
            new \M2E\Core\Model\ControlPanel\OverviewWidget(
                \M2E\Core\Block\Adminhtml\ControlPanel\Widget\Information::class,
                \M2E\Core\Model\ControlPanel\OverviewWidget::FIRST_COLUMN,
                [
                    'environment' => $this->environment,
                    'is_maintenance' => $this->maintenance->isEnabled(),
                ]
            ),
            new \M2E\Core\Model\ControlPanel\OverviewWidget(
                \M2E\Core\Block\Adminhtml\ControlPanel\Widget\Location::class,
                \M2E\Core\Model\ControlPanel\OverviewWidget::FIRST_COLUMN
            ),
            new \M2E\Core\Model\ControlPanel\OverviewWidget(
                \M2E\Core\Block\Adminhtml\ControlPanel\Widget\License::class,
                \M2E\Core\Model\ControlPanel\OverviewWidget::FIRST_COLUMN
            ),
            new \M2E\Core\Model\ControlPanel\OverviewWidget(
                \M2E\Core\Block\Adminhtml\ControlPanel\Widget\Database::class,
                \M2E\Core\Model\ControlPanel\OverviewWidget::SECOND_COLUMN,
                ['table_list' => $this->getTableList()]
            ),
            new \M2E\Core\Model\ControlPanel\OverviewWidget(
                \M2E\Core\Block\Adminhtml\ControlPanel\Widget\Cron::class,
                \M2E\Core\Model\ControlPanel\OverviewWidget::THIRD_COLUMN,
                ['cron_info' => $this->cronInfo]
            ),
            new \M2E\Core\Model\ControlPanel\OverviewWidget(
                \M2E\Core\Block\Adminhtml\ControlPanel\Widget\VersionInfo::class,
                \M2E\Core\Model\ControlPanel\OverviewWidget::THIRD_COLUMN
            ),
        ];
    }

    private function getTableList(): array
    {
        return [
            'Config' => [
                \M2E\Core\Helper\Module\Database\Tables::TABLE_NAME_CONFIG,
                \M2E\Core\Helper\Module\Database\Tables::TABLE_NAME_REGISTRY,
            ],
            'TikTokShop' => [
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_ACCOUNT,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SHOP,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_WAREHOUSE,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_LISTING,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_UNMANAGED_PRODUCT,
                \M2E\Core\Helper\Module\Database\Tables::TABLE_NAME_ATTRIBUTE_MAPPING
            ],
            'Processing' => [
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PROCESSING_LOCK,
            ],
            'Additional' => [
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_LOCK_ITEM,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_SYSTEM_LOG,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_INSTRUCTION,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_ORDER_CHANGE,
                \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_OPERATION_HISTORY,
            ],
        ];
    }
}
