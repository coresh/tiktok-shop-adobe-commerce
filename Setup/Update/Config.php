<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Setup\Update;

class Config implements \M2E\TikTokShop\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            'y24_m02' => [
                \M2E\TikTokShop\Setup\Update\y24_m02\AddSellerNameAccountTable::class,
                \M2E\TikTokShop\Setup\Update\y24_m02\AddProductVariantSku::class,
                \M2E\TikTokShop\Setup\Update\y24_m02\AddSimplePropertyInProduct::class,
            ],
            'y24_m04' => [
                \M2E\TikTokShop\Setup\Update\y24_m04\AddStatusPropertyInVariantSku::class,
                \M2E\TikTokShop\Setup\Update\y24_m04\AddVariantSettingsToScheduledAction::class,
                \M2E\TikTokShop\Setup\Update\y24_m04\DropTableMagentoProductWebsitesUpdate::class,
                \M2E\TikTokShop\Setup\Update\y24_m04\AddMinMaxPricesToProductTable::class,
                \M2E\TikTokShop\Setup\Update\y24_m04\FixScheduledAction::class,
            ],
            'y24_m05' => [
                \M2E\TikTokShop\Setup\Update\y24_m05\AddStatusChangerColumnToScheduledAction::class,
                \M2E\TikTokShop\Setup\Update\y24_m05\UpdateProductStatus::class,
                \M2E\TikTokShop\Setup\Update\y24_m05\DropProductBackupTable::class,
            ],
            'y24_m06' => [
                \M2E\TikTokShop\Setup\Update\y24_m06\ListingWizard::class,
                \M2E\TikTokShop\Setup\Update\y24_m06\RemoveListingProductAddIds::class,
                \M2E\TikTokShop\Setup\Update\y24_m06\RemoveListingProductConfigurations::class,
                \M2E\TikTokShop\Setup\Update\y24_m06\AddOnlineImageColumnToVariantTable::class,
                \M2E\TikTokShop\Setup\Update\y24_m06\ChangeUniqueConstrainInImageTable::class,
                \M2E\TikTokShop\Setup\Update\y24_m06\AddIsSkippedColumnToListingWizard::class,
            ],
            'y24_m07' => [
                \M2E\TikTokShop\Setup\Update\y24_m07\AddBuyerCancellationColumnsToOrder::class,
                \M2E\TikTokShop\Setup\Update\y24_m07\AddBuyerReturnRefundColumnsToOrderItem::class,
            ],
            'y24_m08' => [
                \M2E\TikTokShop\Setup\Update\y24_m08\AddOnlineIdentifierColumns::class,
                \M2E\TikTokShop\Setup\Update\y24_m08\ChangeTemplateDescriptionTable::class,
            ],
            'y24_m09' => [
                \M2E\TikTokShop\Setup\Update\y24_m09\ResetInventoryLastSyncDateInShop::class,
                \M2E\TikTokShop\Setup\Update\y24_m09\RemoveReferencesOfPolicyFromProduct::class,
            ],
            'y24_m10' => [
                \M2E\TikTokShop\Setup\Update\y24_m10\AddPromotion::class,
                \M2E\TikTokShop\Setup\Update\y24_m10\AddColumnsToOrderItem::class,
                \M2E\TikTokShop\Setup\Update\y24_m10\AddUnmanagedProductVariant::class,
            ],
            'y24_m11' => [
                \M2E\TikTokShop\Setup\Update\y24_m11\AddCompliancePolicy::class,
                \M2E\TikTokShop\Setup\Update\y24_m11\AddColumnToCategoryDictionary::class,
                \M2E\TikTokShop\Setup\Update\y24_m11\AddProductListingQuality::class,
            ],
            'y24_m12' => [
                \M2E\TikTokShop\Setup\Update\y24_m12\FixVariantSettingsFieldType::class
            ],
        ];
    }
}
