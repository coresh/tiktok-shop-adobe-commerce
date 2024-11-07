<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ResourceModel\Product\Grid\AllItems;

class Entity extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    public function getIdFieldName()
    {
        return 'entity_id';
    }

    public function isVisibleInSiteVisibility()
    {
        return false;
    }

    public function getProductId(): int
    {
        return (int)$this->getData(\M2E\TikTokShop\Model\ResourceModel\Product\Grid\AllItems\Collection::PRIMARY_COLUMN);
    }
}
