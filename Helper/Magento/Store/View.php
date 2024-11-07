<?php

namespace M2E\TikTokShop\Helper\Magento\Store;

class View
{
    /** @var \Magento\Store\Api\Data\StoreInterface */
    private $defaultStore;
    /** @var \Magento\Store\Model\StoreFactory */
    private $storeFactory;
    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;
    /** @var \M2E\TikTokShop\Helper\Magento\Store\Group */
    private $groupHelper;
    /** @var \M2E\TikTokShop\Helper\Magento\Store\Website */
    private $websiteHelper;

    public function __construct(
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \M2E\TikTokShop\Helper\Magento\Store\Group $groupHelper,
        \M2E\TikTokShop\Helper\Magento\Store\Website $websiteHelper
    ) {
        $this->storeFactory = $storeFactory;
        $this->storeManager = $storeManager;
        $this->groupHelper = $groupHelper;
        $this->websiteHelper = $websiteHelper;
    }

    // ----------------------------------------

    public function isExits($entity)
    {
        if ($entity instanceof \Magento\Store\Model\Store) {
            return (bool)$entity->getCode();
        }

        try {
            $this->storeManager->getStore($entity);
        } catch (\Exception $ex) {
            return false;
        }

        return true;
    }

    public function isChildOfGroup($storeId, $groupId)
    {
        $store = $this->storeManager->getStore($storeId);

        return ($store->getStoreGroupId() == $groupId);
    }

    // ---------------------------------------

    public function isSingleMode()
    {
        return $this->storeFactory->create()->getCollection()->getSize() <= 2;
    }

    public function isMultiMode()
    {
        return !$this->isSingleMode();
    }

    // ----------------------------------------

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefault()
    {
        if ($this->defaultStore === null) {
            $defaultStoreGroup = $this->groupHelper->getDefault();
            $defaultStoreId = $defaultStoreGroup->getDefaultStoreId();

            $this->defaultStore = $this->storeManager->getStore($defaultStoreId);
        }

        return $this->defaultStore;
    }

    public function getDefaultStoreId()
    {
        return (int)$this->getDefault()->getId();
    }

    public function getPath($storeId)
    {
        if ($storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            return (string)__('Admin (Default Values)');
        }

        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $error = (string)__("Store with %store_id doesn't exist.", ['store_id' => $storeId]);
            throw new \M2E\TikTokShop\Model\Exception($error);
        }

        $path = $this->storeManager->getWebsite($store->getWebsiteId())->getName();
        $path .= ' > ' . $this->storeManager->getGroup($store->getStoreGroupId())->getName();
        $path .= ' > ' . $store->getName();

        return $path;
    }

    public function addStore($name, $code, $websiteId, $groupId = null)
    {
        if (!$this->websiteHelper->isExists($websiteId)) {
            $error = (string)__('Website with id %value does not exists.', ['value' => $websiteId]);
            throw new \M2E\TikTokShop\Model\Exception($error);
        }

        try {
            $error = (string)__('Store with %code already exists.', ['code' => $code]);
            throw new \M2E\TikTokShop\Model\Exception($error);
        } catch (\Exception $e) {
            if ($groupId) {
                if (!$this->groupHelper->isChildOfWebsite($groupId, $websiteId)) {
                    $error = (string)__(
                        'Group with id %group_id doesn\'t belong to' .
                        'website with %site_id.',
                        [
                            'group_id' => $groupId,
                            'site_id' => $websiteId,
                        ],
                    );
                    throw new \M2E\TikTokShop\Model\Exception($error);
                }
            } else {
                $groupId = $this->storeManager->getWebsite($websiteId)->getDefaultGroupId();
            }

            $store = $this->storeFactory->create();
            $store->setId(null);

            $store->setWebsite($this->storeManager->getWebsite($websiteId));
            $store->setWebsiteId($websiteId);

            $store->setGroup($this->storeManager->getGroup($groupId));
            $store->setGroupId($groupId);

            $store->setCode($code);
            $store->setName($name);

            $store->save();
            $this->storeManager->reinitStores();

            return $store;
        }
    }
}
