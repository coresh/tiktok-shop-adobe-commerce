<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m02;

use M2E\TikTokShop\Helper\Module\Database\Tables as TablesHelper;
use M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat as SellingFormatResource;

class AddNotSalableToSellingPolicy extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(TablesHelper::TABLE_NAME_TEMPLATE_SELLING_FORMAT);
        $modifier->addColumn(
            SellingFormatResource::COLUMN_IS_NOT_FOR_SALE,
            'SMALLINT UNSIGNED NOT NULL',
            \M2E\TikTokShop\Model\Template\SellingFormat::IS_NOT_FOR_SALE_OFF,
            SellingFormatResource::COLUMN_QTY_MAX_POSTED_VALUE,
            false,
            false
        );

        $modifier->commit();
    }
}
