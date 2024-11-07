<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ActiveRecord;

class Diff
{
    protected array $newSnapshot = [];
    protected array $oldSnapshot = [];

    public function setNewSnapshot(array $snapshot): self
    {
        $this->newSnapshot = $snapshot;

        return $this;
    }

    public function setOldSnapshot(array $snapshot): self
    {
        $this->oldSnapshot = $snapshot;

        return $this;
    }

    public function isDifferent(): bool
    {
        return $this->newSnapshot !== $this->oldSnapshot;
    }

    protected function isSettingsDifferent($keys, $groupKey = null): bool
    {
        $newSnapshotData = $this->newSnapshot;
        if (null !== $groupKey && isset($newSnapshotData[$groupKey])) {
            $newSnapshotData = $newSnapshotData[$groupKey];
        }

        $oldSnapshotData = $this->oldSnapshot;
        if (null !== $groupKey && isset($oldSnapshotData[$groupKey])) {
            $oldSnapshotData = $oldSnapshotData[$groupKey];
        }

        foreach ($keys as $key) {
            if (empty($newSnapshotData[$key]) && empty($oldSnapshotData[$key])) {
                continue;
            }

            if (empty($newSnapshotData[$key]) || empty($oldSnapshotData[$key])) {
                return true;
            }

            if ($newSnapshotData[$key] != $oldSnapshotData[$key]) {
                return true;
            }
        }

        return false;
    }
}
