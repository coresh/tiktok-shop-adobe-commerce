<?php

namespace M2E\TikTokShop\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RecurringData implements InstallDataInterface
{
    private const MINIMUM_REQUIRED_MAGENTO_VERSION = '2.4.0';

    private \M2E\TikTokShop\Model\Setup\InstallChecker $installChecker;
    private \M2E\TikTokShop\Model\Setup\Installer $installer;
    private \M2E\TikTokShop\Model\Setup\Upgrader $upgrader;
    private \Magento\Framework\App\ProductMetadataInterface $productMetadata;
    private \M2E\TikTokShop\Helper\Module\Maintenance $maintenanceHelper;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \M2E\TikTokShop\Helper\Module\Maintenance $maintenanceHelper,
        \M2E\TikTokShop\Model\Setup\InstallChecker $installChecker,
        \M2E\TikTokShop\Model\Setup\Installer $installer,
        \M2E\TikTokShop\Model\Setup\Upgrader $upgrader
    ) {
        $this->installChecker = $installChecker;
        $this->installer = $installer;
        $this->upgrader = $upgrader;
        $this->productMetadata = $productMetadata;
        $this->maintenanceHelper = $maintenanceHelper;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->checkMagentoVersion(
            $this->productMetadata->getVersion(),
            $context->getVersion()
        );

        if (!$this->installChecker->isInstalled()) {
            $this->installer->install($setup);

            return;
        }

        $this->upgrader->upgrade($setup);
    }

    private function checkMagentoVersion(string $magentoVersion, string $moduleVersion): void
    {
        if (!version_compare($magentoVersion, self::MINIMUM_REQUIRED_MAGENTO_VERSION, '>=')) {
            $this->maintenanceHelper->enableDueLowMagentoVersion();
            $this->throwVersionException($magentoVersion, $moduleVersion);
        }
    }

    private function throwVersionException(string $magentoVersion, string $moduleVersion): void
    {
        $message = sprintf(
            'Magento version %s is not compatible with M2E TikTok Shop Connect version %s.',
            $magentoVersion,
            $moduleVersion
        );

        $message .= ' Please upgrade your Magento first or install an older M2E TikTok Shop Connect version 1.35.0';

        throw new \RuntimeException($message);
    }
}
