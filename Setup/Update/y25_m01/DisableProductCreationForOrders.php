<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m01;

class DisableProductCreationForOrders extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $connection = $this->getConnection();
        $tableName = $this->getFullTableName(\M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_ACCOUNT);

        $select = $connection->select()
                             ->from($tableName, ['id', 'magento_orders_settings']);
        $accounts = $connection->fetchAll($select);

        foreach ($accounts as $account) {
            $settingsJson = $account['magento_orders_settings'];

            if (empty($settingsJson)) {
                continue;
            }

            $settings = json_decode($settingsJson, true);

            if (
                isset($settings['listing_other']['product_mode'])
                && $settings['listing_other']['product_mode'] !== 0
            ) {
                $settings['listing_other']['product_mode'] = 0;

                $connection->update(
                    $tableName,
                    ['magento_orders_settings' => json_encode($settings)],
                    ['id = ?' => $account['id']]
                );
            }
        }
    }
}
