<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y25_m03;

class UpdateTemplateCategoryAttributeTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->changeAttributeIdColumn();
    }

    private function changeAttributeIdColumn(): void
    {
        $modifier = $this->createTableModifier(
            \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_TEMPLATE_CATEGORY_ATTRIBUTES
        );

        $modifier->changeColumn(
            \M2E\TikTokShop\Model\ResourceModel\Category\Attribute::COLUMN_ATTRIBUTE_ID,
            'VARCHAR(50)',
            null,
            null,
            false
        );

        $modifier->commit();
    }
}
