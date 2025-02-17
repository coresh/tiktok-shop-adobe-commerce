<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

use M2E\TikTokShop\Helper\Module\Database\Tables as ModuleTablesHelper;

class Module implements \M2E\Core\Model\ModuleInterface
{
    private \M2E\Core\Model\Module\Adapter $adapter;
    private bool $areImportantTablesExist;

    private \M2E\Core\Model\Module $coreModule;
    private \M2E\Core\Model\Module\AdapterFactory $moduleAdapterFactory;
    private \M2E\Core\Helper\Module\Database\Structure $moduleDatabaseHelper;
    private \M2E\TikTokShop\Helper\View\TikTokShop $viewHelper;
    private \M2E\TikTokShop\Model\Config\Manager $configManager;
    private \M2E\TikTokShop\Model\Registry\Manager $registryManager;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;

    public function __construct(
        \M2E\Core\Model\Module $coreModule,
        \M2E\Core\Model\Module\AdapterFactory $moduleAdapterFactory,
        \M2E\Core\Helper\Module\Database\Structure $moduleDatabaseHelper,
        \M2E\TikTokShop\Helper\View\TikTokShop $viewHelper,
        \M2E\TikTokShop\Model\Config\Manager $configManager,
        \M2E\TikTokShop\Model\Registry\Manager $registryManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->coreModule = $coreModule;
        $this->moduleAdapterFactory = $moduleAdapterFactory;
        $this->moduleDatabaseHelper = $moduleDatabaseHelper;
        $this->viewHelper = $viewHelper;
        $this->configManager = $configManager;
        $this->registryManager = $registryManager;
        $this->resourceConnection = $resourceConnection;
    }

    public function getName(): string
    {
        return 'TikTokShop-m2';
    }

    public function getPublicVersion(): string
    {
        return $this->getAdapter()->getPublicVersion();
    }

    public function getSetupVersion(): string
    {
        return $this->getAdapter()->getSetupVersion();
    }

    public function getSchemaVersion(): string
    {
        return $this->getAdapter()->getSchemaVersion();
    }

    public function getDataVersion(): string
    {
        return $this->getAdapter()->getDataVersion();
    }

    public function hasLatestVersion(): bool
    {
        return $this->getAdapter()->hasLatestVersion();
    }

    public function setLatestVersion(string $version): void
    {
        $this->getAdapter()->setLatestVersion($version);
    }

    public function getLatestVersion(): ?string
    {
        return $this->getAdapter()->getLatestVersion();
    }

    public function isDisabled(): bool
    {
        return $this->getAdapter()->isDisabled();
    }

    public function disable(): void
    {
        $this->getAdapter()->disable();
    }

    public function enable(): void
    {
        $this->getAdapter()->enable();
    }

    public function isReadyToWork(): bool
    {
        return $this->coreModule->isReadyToWork()
            && $this->areImportantTablesExist()
            && $this->viewHelper->isInstallationWizardFinished();
    }

    public function areImportantTablesExist(): bool
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->areImportantTablesExist)) {
            return $this->areImportantTablesExist;
        }

        $result = true;
        foreach ([ModuleTablesHelper::TABLE_NAME_WIZARD] as $table) {
            $tableName = $this->moduleDatabaseHelper->getTableNameWithPrefix($table);
            if (!$this->resourceConnection->getConnection()->isTableExists($tableName)) {
                $result = false;
                break;
            }
        }

        return $this->areImportantTablesExist = $result;
    }

    public function getAdapter(): \M2E\Core\Model\Module\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->moduleAdapterFactory->create(
                \M2E\TikTokShop\Helper\Module::IDENTIFIER,
                $this->registryManager->getAdapter(),
                $this->configManager->getAdapter()
            );
        }

        return $this->adapter;
    }
}
