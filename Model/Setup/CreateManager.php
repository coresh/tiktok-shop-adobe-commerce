<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Setup;

use M2E\TikTokShop\Model\ResourceModel\Setup as SetupResource;
use Magento\Framework\DB\Ddl\Table;

class CreateManager
{
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbHelper;
    private SetupResource $resource;
    /** @var \M2E\TikTokShop\Model\ResourceModel\Setup\CollectionFactory */
    private SetupResource\CollectionFactory $collectionFactory;

    public function __construct(
        SetupResource $resource,
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbHelper,
        \M2E\TikTokShop\Model\ResourceModel\Setup\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function initSetupVersion(
        string $versionFrom,
        string $versionTo
    ): \M2E\TikTokShop\Model\Setup {
        $collection = $this->collectionFactory->create();
        if (empty($versionFrom)) {
            $collection->addFieldToFilter(SetupResource::COLUMN_VERSION_FROM, ['null' => true]);
        } else {
            $collection->addFieldToFilter(SetupResource::COLUMN_VERSION_FROM, $versionFrom);
        }

        $collection->addFieldToFilter(SetupResource::COLUMN_VERSION_TO, $versionTo);
        $collection->getSelect()
                   ->limit(1);

        $setupObject = $collection->getFirstItem();

        if (!$setupObject->getId()) {
            $setupObject->setData(
                [
                    SetupResource::COLUMN_VERSION_FROM => empty($versionFrom) ? null : $versionFrom,
                    SetupResource::COLUMN_VERSION_TO => $versionTo,
                    SetupResource::COLUMN_IS_COMPLETED => 0,
                ],
            );
            $setupObject->save();
        }

        return $setupObject;
    }
}
