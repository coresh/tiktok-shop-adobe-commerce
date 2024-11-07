<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel\Inspection\Repository;

class DefinitionProvider
{
    public const GROUP_PRODUCTS = 'products';
    public const GROUP_STRUCTURE = 'structure';
    public const GROUP_GENERAL = 'general';

    public const EXECUTION_SPEED_SLOW = 'slow';
    public const EXECUTION_SPEED_FAST = 'fast';

    /** @var array[] */
    private array $inspectionsData = [
        [
            'nick' => 'ExtensionCron',
            'title' => 'Extension Cron',
            'description' => '
            - Cron [runner] does not work<br>
            - Cron [runner] is not working more than 30 min<br>
            - Cron [runner] is disabled by developer
            ',
            'group' => self::GROUP_GENERAL,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\ExtensionCron::class,
        ],
        [
            'nick' => 'FilesPermissions',
            'title' => 'Files and Folders permissions',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_SLOW,
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
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\MagentoSettings::class,
        ],
        [
            'nick' => 'NonexistentTemplates',
            'title' => 'Nonexistent template',
            'description' => '',
            'group' => self::GROUP_PRODUCTS,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\NonexistentTemplates::class,
        ],
        [
            'nick' => 'ServerConnection',
            'title' => 'Connection with server',
            'description' => '',
            'group' => self::GROUP_GENERAL,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\ServerConnection::class,
        ],
        [
            'nick' => 'ShowTikTokShopLoggers',
            'title' => 'Show TikTokShop loggers',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_SLOW,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\ShowModuleLoggers::class,
        ],
        [
            'nick' => 'ConfigsValidity',
            'title' => 'Configs validity',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\ConfigsValidity::class,
        ],
        [
            'nick' => 'FilesValidity',
            'title' => 'Files validity',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\FilesValidity::class,
        ],
        [
            'nick' => 'TablesStructureValidity',
            'title' => 'Tables structure validity',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => \M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector\TablesStructureValidity::class,
        ],
    ];

    private \M2E\TikTokShop\Model\ControlPanel\Inspection\DefinitionFactory $definitionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ControlPanel\Inspection\DefinitionFactory $definitionFactory
    ) {
        $this->definitionFactory = $definitionFactory;
    }

    /**
     * @return \M2E\TikTokShop\Model\ControlPanel\Inspection\Definition[]
     */
    public function getDefinitions(): array
    {
        $definitions = [];

        foreach ($this->inspectionsData as $inspectionData) {
            $definitions[] = $this->definitionFactory->create(
                $inspectionData['nick'],
                $inspectionData['title'],
                $inspectionData['description'],
                $inspectionData['group'],
                $inspectionData['execution_speed_group'],
                $inspectionData['handler'],
            );
        }

        return $definitions;
    }
}
