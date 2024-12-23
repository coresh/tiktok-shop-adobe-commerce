<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m11;

use M2E\TikTokShop\Helper\Module\Database\Tables;
use Magento\Framework\DB\Ddl\Table;

class AddColumnToCategoryDictionary extends \M2E\TikTokShop\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_CATEGORY_DICTIONARY);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_IS_VALID,
            Table::TYPE_BOOLEAN,
            1,
            \M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::COLUMN_HAS_REQUIRED_PRODUCT_ATTRIBUTES,
            false,
            false
        );

        $modifier->commit();
    }
}
