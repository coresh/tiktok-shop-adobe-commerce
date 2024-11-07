<?php

namespace M2E\TikTokShop\Model\Setup;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SetupInterface;

class Installer
{
    public const LONG_COLUMN_SIZE = 16777217;

    private SetupInterface $setup;

    // ----------------------------------------

    private \M2E\TikTokShop\Model\Setup\Repository $setupRepository;
    /** @var \M2E\TikTokShop\Model\Setup\InstallHandlerRepository */
    private InstallHandlerRepository $installerRepository;
    private \Magento\Framework\App\DeploymentConfig $deploymentConfig;
    private \Psr\Log\LoggerInterface $logger;
    private \Magento\Framework\Module\ModuleListInterface $moduleList;
    private \M2E\TikTokShop\Helper\Module\Maintenance $maintenance;

    public function __construct(
        Repository $setupRepository,
        \M2E\TikTokShop\Model\Setup\InstallHandlerRepository $installerRepository,
        \M2E\TikTokShop\Helper\Module\Maintenance $maintenance,
        \M2E\TikTokShop\Setup\LoggerFactory $loggerFactory,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig
    ) {
        $this->setupRepository = $setupRepository;
        $this->installerRepository = $installerRepository;
        $this->deploymentConfig = $deploymentConfig;
        $this->maintenance = $maintenance;
        $this->moduleList = $moduleList;
        $this->logger = $loggerFactory->create();
    }

    /**
     * Module versions from setup_module magento table uses only by magento for run install or upgrade files.
     * We do not use these versions in setup & upgrade logic (only set correct values to it, using domain_setup table).
     * So version, that presented in $context parameter, is not used.
     *
     * @param SetupInterface $setup
     */
    public function install(SetupInterface $setup): void
    {
        $this->setup = $setup;

        $this->maintenance->enable();
        $this->setup->startSetup();

        try {
            $this->dropTables();

            $this->setupRepository->createTable();
            $setupObject = $this->setupRepository->create(null, $this->getCurrentVersion());

            $this->installSchema($this->installerRepository->getAll());
            $this->installData($this->installerRepository->getAll());
        } catch (\Throwable $exception) {
            $this->logger->error($exception, ['source' => 'Install']);

            if (isset($setupObject)) {
                $setupObject->setProfilerData($exception->__toString());

                $this->setupRepository->save($setupObject);
            }

            $this->setup->endSetup();

            return;
        }

        $setupObject->markAsCompleted();
        $this->setupRepository->save($setupObject);

        $this->maintenance->disable();
        $this->setup->endSetup();
    }

    private function dropTables(): void
    {
        $likeCondition = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX)
            . \M2E\TikTokShop\Helper\Module\Database\Tables::PREFIX
            . '%';

        $tables = $this->getConnection()->getTables($likeCondition);

        foreach ($tables as $table) {
            $this->getConnection()->dropTable($table);
        }
    }

    /**
     * @param \M2E\TikTokShop\Model\Setup\InstallHandlerInterface[] $handlers
     */
    private function installSchema(array $handlers): void
    {
        foreach ($handlers as $handler) {
            $handler->installSchema($this->setup);
        }
    }

    /**
     * @param \M2E\TikTokShop\Model\Setup\InstallHandlerInterface[] $handlers
     */
    private function installData(array $handlers): void
    {
        foreach ($handlers as $handler) {
            $handler->installData($this->setup);
        }
    }

    private function getConnection(): AdapterInterface
    {
        return $this->setup->getConnection();
    }

    private function getCurrentVersion(): string
    {
        return $this->moduleList->getOne(\M2E\TikTokShop\Helper\Module::IDENTIFIER)['setup_version'];
    }
}
