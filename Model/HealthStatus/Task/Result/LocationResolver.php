<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\HealthStatus\Task\Result;

use M2E\TikTokShop\Model\HealthStatus\Task;

class LocationResolver extends \M2E\TikTokShop\Model\AbstractModel
{
    private const KEY_TAB = 'tab';
    private const KEY_FIELD_SET = 'field_set';
    private const KEY_FIELD = 'field';

    public function resolveTabName(Task\AbstractModel $task): string
    {
        $result = $this->usingMap($task);
        if ($result === null) {
            $className = get_class($task);
            throw new \LogicException("Unable to create Result object for task [{$className}]");
        }

        return $result[self::KEY_TAB];
    }

    public function resolveFieldSetName(Task\AbstractModel $task): string
    {
        $result = $this->usingMap($task);
        if ($result === null) {
            $className = get_class($task);
            throw new \LogicException("Unable to create Result object for task [{$className}]");
        }

        return $result[self::KEY_FIELD_SET];
    }

    public function resolveFieldName(Task\AbstractModel $task): string
    {
        $result = $this->usingMap($task);
        if ($result === null) {
            $className = get_class($task);
            throw new \LogicException("Unable to create Result object for task [{$className}]");
        }

        return $result[self::KEY_FIELD];
    }

    private function usingMap(Task\AbstractModel $task): ?array
    {
        $key = \M2E\TikTokShop\Helper\Client::getClassName($task);

        return $this->getMap()[$key] ?? null;
    }

    private function getMap(): array
    {
        return [
            \M2E\TikTokShop\Model\HealthStatus\Task\Database\MysqlInfo\CrashedTables::class => [
                self::KEY_TAB => (string)__('Problems'),
                self::KEY_FIELD_SET => (string)__('Database'),
                self::KEY_FIELD => (string)__('Crashed Tables'),
            ],
            \M2E\TikTokShop\Model\HealthStatus\Task\Database\MysqlInfo\TablesStructure::class => [
                self::KEY_TAB => (string)__('Problems'),
                self::KEY_FIELD_SET => (string)__('Database'),
                self::KEY_FIELD => (string)__('Scheme (tables, columns)'),
            ],
            \M2E\TikTokShop\Model\HealthStatus\Task\Server\Status\SystemLogs::class => [
                self::KEY_TAB => (string)__('Problems'),
                self::KEY_FIELD_SET => (string)__('Server'),
                self::KEY_FIELD => (string)__('System Log'),
            ],
            \M2E\TikTokShop\Model\HealthStatus\Task\Orders\MagentoCreationFailed::class => [
                self::KEY_TAB => (string)__('Problems'),
                self::KEY_FIELD_SET => (string)__('Orders'),
                self::KEY_FIELD => (string)__('Magento Order Creation'),
            ],
        ];
    }
}
