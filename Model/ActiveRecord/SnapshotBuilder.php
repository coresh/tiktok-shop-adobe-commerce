<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ActiveRecord;

class SnapshotBuilder
{
    protected AbstractModel $model;

    public function setModel(AbstractModel $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): AbstractModel
    {
        return $this->model;
    }

    public function getSnapshot(): array
    {
        $data = (array)$this->getModel()->getData();

        foreach ($data as &$value) {
            (null !== $value && !is_array($value)) && $value = (string)$value;
        }

        return $data;
    }
}
