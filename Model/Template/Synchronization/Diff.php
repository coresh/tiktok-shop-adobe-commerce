<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Synchronization;

use M2E\TikTokShop\Model\ResourceModel\Template\Synchronization as SynchronizationResource;

class Diff extends \M2E\TikTokShop\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isListModeEnabled()
            || $this->isListModeDisabled()
            || $this->isListSettingsChanged()
            || $this->isRelistModeEnabled()
            || $this->isRelistModeDisabled()
            || $this->isRelistSettingsChanged()
            || $this->isStopModeEnabled()
            || $this->isStopModeDisabled()
            || $this->isStopSettingsChanged()
            || $this->isReviseSettingsChanged();
    }

    public function isListModeEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_LIST_MODE])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_LIST_MODE]);
    }

    public function isListModeDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_LIST_MODE])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_LIST_MODE]);
    }

    public function isListSettingsChanged(): bool
    {
        $keys = [
            SynchronizationResource::COLUMN_LIST_STATUS_ENABLED,
            SynchronizationResource::COLUMN_LIST_IS_IN_STOCK,
            SynchronizationResource::COLUMN_LIST_QTY_CALCULATED,
            SynchronizationResource::COLUMN_LIST_QTY_CALCULATED_VALUE,
            SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_MODE,
            SynchronizationResource::COLUMN_LIST_ADVANCED_RULES_FILTERS,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isRelistModeEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_RELIST_MODE])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_RELIST_MODE]);
    }

    public function isRelistModeDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_RELIST_MODE])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_RELIST_MODE]);
    }

    public function isRelistSettingsChanged(): bool
    {
        $keys = [
            SynchronizationResource::COLUMN_RELIST_FILTER_USER_LOCK,
            SynchronizationResource::COLUMN_RELIST_STATUS_ENABLED,
            SynchronizationResource::COLUMN_RELIST_IS_IN_STOCK,
            SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED,
            SynchronizationResource::COLUMN_RELIST_QTY_CALCULATED_VALUE,
            SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_MODE,
            SynchronizationResource::COLUMN_RELIST_ADVANCED_RULES_FILTERS,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isStopModeEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_STOP_MODE])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_STOP_MODE]);
    }

    public function isStopModeDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_STOP_MODE])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_STOP_MODE]);
    }

    public function isStopSettingsChanged(): bool
    {
        $keys = [
            SynchronizationResource::COLUMN_STOP_STATUS_DISABLED,
            SynchronizationResource::COLUMN_STOP_OUT_OFF_STOCK,
            SynchronizationResource::COLUMN_STOP_QTY_CALCULATED,
            SynchronizationResource::COLUMN_STOP_QTY_CALCULATED_VALUE,
            SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_MODE,
            SynchronizationResource::COLUMN_STOP_ADVANCED_RULES_FILTERS,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isReviseSettingsChanged(): bool
    {
        return $this->isReviseQtyEnabled()
            || $this->isReviseQtyDisabled()
            || $this->isReviseQtySettingsChanged()
            || $this->isRevisePriceEnabled()
            || $this->isRevisePriceDisabled()
            || $this->isReviseTitleEnabled()
            || $this->isReviseTitleDisabled()
            || $this->isReviseDescriptionEnabled()
            || $this->isReviseDescriptionDisabled()
            || $this->isReviseImagesEnabled()
            || $this->isReviseImagesDisabled()
            || $this->isReviseCategoriesEnabled()
            || $this->isReviseCategoriesDisabled()
            || $this->isReviseOtherEnabled()
            || $this->isReviseOtherDisabled()
            || $this->isReviseComplianceEnabled()
            || $this->isReviseCategoriesDisabled();
    }

    public function isReviseQtyEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_QTY])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_QTY]);
    }

    public function isReviseQtyDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_QTY])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_QTY]);
    }

    public function isReviseQtySettingsChanged(): bool
    {
        $keys = [
            SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_MODE,
            SynchronizationResource::COLUMN_REVISE_UPDATE_QTY_MAX_APPLIED_VALUE,
        ];

        return $this->isSettingsDifferent($keys);
    }

    public function isRevisePriceEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE]);
    }

    public function isRevisePriceDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE]);
    }

    public function isReviseTitleEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE]);
    }

    public function isReviseTitleDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE]);
    }

    public function isReviseDescriptionEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION]);
    }

    public function isReviseDescriptionDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION]);
    }

    public function isReviseImagesEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES]);
    }

    public function isReviseImagesDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES]);
    }

    public function isReviseCategoriesEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES]);
    }

    public function isReviseCategoriesDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES]);
    }

    public function isReviseOtherEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_OTHER])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_OTHER]);
    }

    public function isReviseOtherDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_OTHER])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_OTHER]);
    }

    public function isReviseComplianceEnabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_COMPLIANCE])
            && !empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_COMPLIANCE]);
    }

    public function isReviseComplianceDisabled(): bool
    {
        $newSnapshotData = $this->newSnapshot;
        $oldSnapshotData = $this->oldSnapshot;

        return !empty($oldSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_COMPLIANCE])
            && empty($newSnapshotData[SynchronizationResource::COLUMN_REVISE_UPDATE_COMPLIANCE]);
    }
}
