<?php

namespace M2E\TikTokShop\Model\ResourceModel\Tag;

class Collection extends \M2E\TikTokShop\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    /**
     * @inerhitDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \M2E\TikTokShop\Model\Tag\Entity::class,
            \M2E\TikTokShop\Model\ResourceModel\Tag::class
        );
    }

    /**
     * @return \M2E\TikTokShop\Model\Tag\Entity[]
     */
    public function getItemsWithoutHasErrorsTag(): array
    {
        $this->getSelect()->where('error_code != (?)', \M2E\TikTokShop\Model\Tag::HAS_ERROR_ERROR_CODE);

        return $this->getAll();
    }

    /**
     * @return \M2E\TikTokShop\Model\Tag\Entity[]
     */
    public function getAll(): array
    {
        return $this->getItems();
    }
}
