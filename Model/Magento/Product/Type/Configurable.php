<?php

/**
 * Due to strange changes in addStoreFilter method since Magento version 1.9.x,
 * we were forced to setStore for collection manually
 */

namespace M2E\TikTokShop\Model\Magento\Product\Type;

class Configurable extends \Magento\ConfigurableProduct\Model\Product\Type\Configurable
{
    /**
     * {@inheritdoc}
     */
    public function getUsedProductCollection($product = null)
    {
        $collection = parent::getUsedProductCollection($product);

        if ($this->getStoreFilter($product) !== null) {
            $collection->setStoreId($this->getStoreFilter($product));
        }

        return $collection;
    }

    public function cleanProductCache($product)
    {
        $cache = \Magento\Framework\App\ObjectManager::getInstance()
                                                     ->get(\Magento\Framework\App\Cache\Type\Collection::class);
        $cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::TYPE_CODE . '_' . $product->getId()]);
    }
}
