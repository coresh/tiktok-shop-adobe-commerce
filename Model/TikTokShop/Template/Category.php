<?php

namespace M2E\TikTokShop\Model\TikTokShop\Template;

class Category extends \M2E\TikTokShop\Model\ActiveRecord\AbstractModel
{
    public const CATEGORY_MODE_NONE = 0;
    public const CATEGORY_MODE_TTS = 1;
    public const CATEGORY_MODE_ATTRIBUTE = 2;

    public const MODE_ITEM_SPECIFICS = 1;
    public const MODE_CUSTOM_ITEM_SPECIFICS = 3;

    public const VALUE_MODE_NONE = 0;
    public const VALUE_MODE_TTS_RECOMMENDED = 1;
    public const VALUE_MODE_CUSTOM_VALUE = 2;
    public const VALUE_MODE_CUSTOM_ATTRIBUTE = 3;
    public const VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE = 4;

    public const RENDER_TYPE_TEXT = 'text';
    public const RENDER_TYPE_SELECT_ONE = 'select_one';
    public const RENDER_TYPE_SELECT_MULTIPLE = 'select_multiple';
    public const RENDER_TYPE_SELECT_ONE_OR_TEXT = 'select_one_or_text';
    public const RENDER_TYPE_SELECT_MULTIPLE_OR_TEXT = 'select_multiple_or_text';

    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->cache = $cache;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(\M2E\TikTokShop\Model\ResourceModel\Category\Dictionary::class);
    }

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        $collection = $this->listingProductCollectionFactory->create();
        $collection->getSelect()->where(
            'template_category_id = ? OR template_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        return false;
    }

    public function save()
    {
        $this->cache->removeTagValues('tts_template_category');

        return parent::save();
    }

    public function delete()
    {
        if ($this->isLocked()) {
            return false;
        }

        return parent::delete();
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->getData('category_id');
    }

    // ---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    /**
     * @return array
     */
    public function getCategorySource()
    {
        return [
            'mode' => $this->getData('category_mode'),
            'value' => $this->getData('category_id'),
            'path' => $this->getData('category_path'),
            'attribute' => $this->getData('category_attribute'),
        ];
    }
}
