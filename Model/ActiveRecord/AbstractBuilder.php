<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ActiveRecord;

abstract class AbstractBuilder
{
    protected AbstractModel $model;
    protected array $rawData;

    abstract protected function prepareData(): array;

    abstract public function getDefaultData(): array;

    /**
     * @psalm-template T of \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
     * @param T $model
     * @param array $rawData
     *
     * @return T
     */
    public function build(AbstractModel $model, array $rawData): AbstractModel
    {
        if (empty($rawData)) {
            return $model;
        }

        $this->model = $model;
        $this->rawData = $rawData;

        $preparedData = $this->prepareData();
        $this->model->addData($preparedData);

        return $this->model;
    }

    public function getModel(): AbstractModel
    {
        return $this->model;
    }
}
