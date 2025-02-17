<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{
    private \M2E\Core\Model\Setup\UninstallFactory $uninstallFactory;
    private \M2E\TikTokShop\Setup\InstallTablesListResolver $installTablesListResolver;
    private \M2E\TikTokShop\Model\Config\Manager $configManager;
    private \M2E\TikTokShop\Model\VariablesDir $variablesDir;
    private \M2E\TikTokShop\Setup\MagentoCoreConfigSettings $magentoCoreConfigSettings;

    public function __construct(
        \M2E\Core\Model\Setup\UninstallFactory $uninstallFactory,
        \M2E\TikTokShop\Setup\InstallTablesListResolver $installTablesListResolver,
        \M2E\TikTokShop\Model\Config\Manager $configManager,
        \M2E\TikTokShop\Model\VariablesDir $variablesDir,
        \M2E\TikTokShop\Setup\MagentoCoreConfigSettings $magentoCoreConfigSettings
    ) {
        $this->uninstallFactory = $uninstallFactory;
        $this->installTablesListResolver = $installTablesListResolver;
        $this->configManager = $configManager;
        $this->variablesDir = $variablesDir;
        $this->magentoCoreConfigSettings = $magentoCoreConfigSettings;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->uninstallFactory
            ->create(
                \M2E\TikTokShop\Helper\Module::IDENTIFIER,
                $this->installTablesListResolver,
                $this->configManager->getAdapter(),
                $this->variablesDir->getAdapter(),
                $this->magentoCoreConfigSettings,
                $setup,
            )
            ->process();
    }
}
