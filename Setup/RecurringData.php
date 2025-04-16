<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RecurringData implements InstallDataInterface
{
    private const MINIMUM_REQUIRED_MAGENTO_VERSION = '2.4.0';

    private \Magento\Framework\App\ProductMetadataInterface $productMetadata;
    private \M2E\TikTokShop\Helper\Module\Maintenance $maintenanceHelper;
    private \M2E\TikTokShop\Setup\InstallHandlerCollection $installHandlerCollection;
    private \M2E\TikTokShop\Setup\InstallTablesListResolver $installTablesListResolver;
    private \M2E\TikTokShop\Setup\UpgradeCollection $upgradeCollection;
    private \M2E\Core\Model\Setup\InstallChecker $installChecker;
    private \M2E\Core\Model\Setup\InstallerFactory $installerFactory;
    private \M2E\Core\Model\Setup\UpgraderFactory $upgraderFactory;
    private \M2E\TikTokShop\Setup\MigrateToCore $migrateToCore;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \M2E\TikTokShop\Helper\Module\Maintenance $maintenance,
        \M2E\TikTokShop\Setup\InstallHandlerCollection $installHandlerCollection,
        \M2E\TikTokShop\Setup\InstallTablesListResolver $installTablesListResolver,
        \M2E\TikTokShop\Setup\UpgradeCollection $upgradeCollection,
        \M2E\Core\Model\Setup\InstallChecker $installChecker,
        \M2E\Core\Model\Setup\InstallerFactory $installerFactory,
        \M2E\Core\Model\Setup\UpgraderFactory $upgraderFactory,
        \M2E\TikTokShop\Setup\MigrateToCore $migrateToCore
    ) {
        $this->productMetadata = $productMetadata;
        $this->maintenanceHelper = $maintenance;
        $this->installHandlerCollection = $installHandlerCollection;
        $this->installTablesListResolver = $installTablesListResolver;
        $this->upgradeCollection = $upgradeCollection;
        $this->installChecker = $installChecker;
        $this->installerFactory = $installerFactory;
        $this->upgraderFactory = $upgraderFactory;
        $this->migrateToCore = $migrateToCore;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->checkMagentoVersion($this->productMetadata->getVersion());

        if ($this->migrateToCore->isNeedMigrate()) {
            $this->migrateToCore->migrate($setup->getConnection());
        }

        if (!$this->installChecker->isInstalled(\M2E\TikTokShop\Helper\Module::IDENTIFIER)) {
            $this->installerFactory->create(
                \M2E\TikTokShop\Helper\Module::IDENTIFIER,
                $this->installHandlerCollection,
                $this->installTablesListResolver,
                $setup,
                $this->maintenanceHelper
            )->install();

            return;
        }

        $this->upgraderFactory->create(
            \M2E\TikTokShop\Helper\Module::IDENTIFIER,
            $this->upgradeCollection,
            $setup
        )->upgrade();

        $this->maintenanceHelper->disable();
    }

    private function checkMagentoVersion(string $magentoVersion): void
    {
        if (!version_compare($magentoVersion, self::MINIMUM_REQUIRED_MAGENTO_VERSION, '>=')) {
            $this->maintenanceHelper->enableDueLowMagentoVersion();

            $message = sprintf(
                'Magento version %s is not compatible with %s version. ' .
                'Please upgrade your Magento first.',
                $magentoVersion,
                \M2E\TikTokShop\Helper\Module::getExtensionTitle()
            );

            throw new \RuntimeException($message);
        }
    }
}
