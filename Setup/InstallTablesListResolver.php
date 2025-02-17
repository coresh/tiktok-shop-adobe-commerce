<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup;

class InstallTablesListResolver implements \M2E\Core\Model\Setup\InstallTablesListResolverInterface
{
    private \M2E\Core\Helper\Magento $magentoHelper;

    public function __construct(\M2E\Core\Helper\Magento $magentoHelper)
    {
        $this->magentoHelper = $magentoHelper;
    }

    public function list(\Magento\Framework\DB\Adapter\AdapterInterface $connection): array
    {
        $likeCondition = $this->magentoHelper->getDatabaseTablesPrefix()
            . \M2E\TikTokShop\Helper\Module\Database\Tables::PREFIX
            . '%';

        return $connection->getTables($likeCondition);
    }
}
