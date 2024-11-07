<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template;

abstract class AffectedListingsProductsAbstract
{
    private ?\M2E\TikTokShop\Model\ActiveRecord\AbstractModel $model = null;

    abstract public function loadListingProductCollection(
        array $filters = []
    ): \M2E\TikTokShop\Model\ResourceModel\Product\Collection;

    public function setModel(\M2E\TikTokShop\Model\ActiveRecord\AbstractModel $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): ?\M2E\TikTokShop\Model\ActiveRecord\AbstractModel
    {
        return $this->model;
    }

    public function getObjectsData($columns = '*', array $filters = []): array
    {
        $productCollection = $this->loadListingProductCollection($filters);

        if (is_array($columns) && !empty($columns)) {
            $productCollection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
            $productCollection->getSelect()->columns($columns);
        }

        return (array)$productCollection->getData();
    }

    public function getIds(array $filters = []): array
    {
        return $this->loadListingProductCollection($filters)->getAllIds();
    }
}
