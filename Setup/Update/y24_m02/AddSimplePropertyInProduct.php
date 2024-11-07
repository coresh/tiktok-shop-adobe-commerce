<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m02;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Product as ListingProductResource;

class AddSimplePropertyInProduct extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_PRODUCT);
        $modifier->addColumn(
            ListingProductResource::COLUMN_IS_SIMPLE,
            'SMALLINT NOT NULL',
            '1',
            ListingProductResource::COLUMN_TTS_PRODUCT_ID,
        );
    }
}
