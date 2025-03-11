<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m02;

use M2E\Core\Helper\Module\Database\Tables as CoreTables;

class MigrateLicenseAndRegistrationUserToCore extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    private \M2E\Core\Model\RegistryManager $coreRegistryManager;

    public function __construct(
        \M2E\Core\Model\RegistryManager $coreRegistryManager,
        \M2E\Core\Model\Setup\Database\Modifier\ConfigFactory $modifierConfigFactory,
        \M2E\Core\Model\Setup\Database\Modifier\TableFactory $modifierTableFactory,
        \M2E\Core\Helper\Module\Database\Tables $tablesHelper,
        \Magento\Framework\Module\Setup $installer
    ) {
        parent::__construct($modifierConfigFactory, $modifierTableFactory, $tablesHelper, $installer);
        $this->coreRegistryManager = $coreRegistryManager;
    }

    public function execute(): void
    {
        $this->migrateLicenseToCore();
        $this->migrateRegistrationUser();
    }

    private function migrateLicenseToCore(): void
    {
        $coreConfig = $this->getConfigModifier(\M2E\Core\Helper\Module::IDENTIFIER);

        $connection = $this->getConnection();
        $oldConfigTable = $this->getFullTableName('m2e_tts_config');
        $newConfigTable = $this->getFullTableName(CoreTables::TABLE_NAME_CONFIG);

        if (!$connection->isTableExists($oldConfigTable)) {
            return;
        }

        $configPaths = [
            ['/license/', 'key'],
            ['/license/domain/', 'real'],
            ['/license/domain/', 'is_valid'],
            ['/license/domain/', 'valid'],
            ['/license/ip/', 'real'],
            ['/license/ip/', 'valid'],
            ['/license/ip/', 'is_valid'],
            ['/license/info/', 'email'],
            ['/location/', 'domain'],
            ['/location/', 'ip'],
        ];

        foreach ($configPaths as [$group, $key]) {
            $oldConfigValue = $connection->fetchOne(
                $connection->select()
                           ->from($oldConfigTable, ['value'])
                           ->where('`group` = ?', $group)
                           ->where('`key` = ?', $key)
            );

            if ($oldConfigValue === null) {
                continue;
            }

            $newConfig = $coreConfig->getEntity($group, $key);

            if ($newConfig->getValue() !== null) {
                $connection->delete(
                    $oldConfigTable,
                    [
                        '`group` = ?' => $group,
                        '`key` = ?' => $key,
                    ]
                );
                continue;
            }

            $connection->update(
                $newConfigTable,
                ['value' => $oldConfigValue],
                [
                    '`group` = ?' => $group,
                    '`key` = ?' => $key,
                ]
            );

            $connection->delete(
                $oldConfigTable,
                [
                    '`group` = ?' => $group,
                    '`key` = ?' => $key,
                ]
            );
        }
    }

    private function migrateRegistrationUser(): void
    {
        $registryTable = $this->getFullTableName('m2e_tts_registry');
        $connection = $this->getConnection();

        if (!$connection->isTableExists($registryTable)) {
            return;
        }

        $user = $connection
            ->select()
            ->from($registryTable, ['value'])
            ->where('`key` = ?', '/registration/user_info/')
            ->query()
            ->fetchColumn();

        if ($user === false) {
            return;
        }

        $newUser = $this->coreRegistryManager->get('/registration/user/');

        if ($newUser !== null) {
            $connection->delete(
                $registryTable,
                ['`key` = ?' => '/registration/user_info/']
            );
            return;
        }

        $this->coreRegistryManager->set('/registration/user/', $user);
        $connection->delete(
            $registryTable,
            ['`key` = ?' => '/registration/user_info/']
        );
    }
}
