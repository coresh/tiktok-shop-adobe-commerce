<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel;

class InspectionTaskProvider implements \M2E\Core\Model\ControlPanel\Inspection\TaskProviderInterface
{
    /** @var array[] */
    private array $definitions = [
        [
            'nick' => 'ExtensionCron',
            'title' => 'Extension Cron',
            'description' => '
            - Cron [runner] does not work<br>
            - Cron [runner] is not working more than 30 min<br>
            - Cron [runner] is disabled by developer
            ',
            'group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::GROUP_GENERAL,
            'execution_speed_group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\ExtensionCron::class,
        ],
        [
            'nick' => 'FilesPermissions',
            'title' => 'Files and Folders permissions',
            'description' => '',
            'group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::GROUP_STRUCTURE,
            'execution_speed_group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::EXECUTION_SPEED_SLOW,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\FilesPermissions::class,
        ],
        [
            'nick' => 'MagentoSettings',
            'title' => 'Magento settings',
            'description' => '
            - Non-default Magento timezone set<br>
            - GD library is installed<br>
            - [APC|Memchached|Redis] Cache is enabled<br>
            ',
            'group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::GROUP_STRUCTURE,
            'execution_speed_group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\MagentoSettings::class,
        ],
        [
            'nick' => 'ServerConnection',
            'title' => 'Connection with server',
            'description' => '',
            'group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::GROUP_GENERAL,
            'execution_speed_group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\ServerConnection::class,
        ],
        [
            'nick' => 'ShowTikTokShopLoggers',
            'title' => 'Show TikTokShop loggers',
            'description' => '',
            'group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::GROUP_STRUCTURE,
            'execution_speed_group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::EXECUTION_SPEED_SLOW,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\ShowModuleLoggers::class,
        ],
        [
            'nick' => 'ConfigsValidity',
            'title' => 'Configs validity',
            'description' => '',
            'group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::GROUP_STRUCTURE,
            'execution_speed_group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\ConfigsValidity::class,
        ],
        [
            'nick' => 'FilesValidity',
            'title' => 'Files validity',
            'description' => '',
            'group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::GROUP_STRUCTURE,
            'execution_speed_group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\FilesValidity::class,
        ],
        [
            'nick' => 'TablesStructureValidity',
            'title' => 'Tables structure validity',
            'description' => '',
            'group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::GROUP_STRUCTURE,
            'execution_speed_group' => \M2E\Core\Model\ControlPanel\InspectionTaskDefinition::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\TablesStructureValidity::class,
        ],
    ];

    public function getExtensionModuleName(): string
    {
        return \M2E\TikTokShop\Model\ControlPanel\Extension::NAME;
    }

    /**
     * @return \M2E\Core\Model\ControlPanel\InspectionTaskDefinition[]
     */
    public function getTasks(): array
    {
        $result = [];

        foreach ($this->definitions as $item) {
            $result[$item['nick']] = new \M2E\Core\Model\ControlPanel\InspectionTaskDefinition(
                $item['nick'],
                $item['title'],
                $item['description'],
                $item['group'],
                $item['execution_speed_group'],
                $item['handler'],
            );
        }

        return array_values($result);
    }
}
