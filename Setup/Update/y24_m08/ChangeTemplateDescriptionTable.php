<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update\y24_m08;

use M2E\TikTokShop\Helper\Module\Database\Tables;

class ChangeTemplateDescriptionTable extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->dropGalleryTypeColumn();
        $this->addResizeImageColumn();
    }

    private function dropGalleryTypeColumn()
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_DESCRIPTION);
        $modifier->dropColumn('gallery_type', false, false);
        $modifier->commit();
    }

    private function addResizeImageColumn()
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_DESCRIPTION);

        $modifier->addColumn(
            \M2E\TikTokShop\Model\ResourceModel\Template\Description::COLUMN_RESIZE_IMAGE,
            'SMALLINT UNSIGNED NOT NULL',
            1,
            \M2E\TikTokShop\Model\ResourceModel\Template\Description::COLUMN_GALLERY_IMAGES_ATTRIBUTE,
            false,
            false
        );

        $modifier->commit();
    }
}
