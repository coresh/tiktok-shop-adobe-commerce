<?php

namespace M2E\TikTokShop\Helper\Magento;

class Store
{
    /** @var \Magento\Store\Api\Data\WebsiteInterface */
    private $defaultWebsite;
    /** @var \Magento\Store\Api\Data\GroupInterface */
    private $defaultStoreGroup;
    /** @var \Magento\Store\Api\Data\StoreInterface */
    private $defaultStore;
    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    public function isSingleStoreMode(): bool
    {
        return count($this->storeManager->getStores(true)) <= 2;
    }

    public function isMultiStoreMode(): bool
    {
        return !$this->isSingleStoreMode();
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultWebsite(): \Magento\Store\Api\Data\WebsiteInterface
    {
        if ($this->defaultWebsite === null) {
            $this->defaultWebsite = $this->storeManager->getWebsite(true);
        }

        return $this->defaultWebsite;
    }

    /**
     * @return \Magento\Store\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultStoreGroup(): \Magento\Store\Api\Data\GroupInterface
    {
        if ($this->defaultStoreGroup === null) {
            $defaultWebsite = $this->getDefaultWebsite();
            $defaultStoreGroupId = $defaultWebsite->getDefaultGroupId();

            $this->defaultStoreGroup = $this->storeManager->getGroup($defaultStoreGroupId);
        }

        return $this->defaultStoreGroup;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultStore(): \Magento\Store\Api\Data\StoreInterface
    {
        if ($this->defaultStore === null) {
            $defaultStoreGroup = $this->getDefaultStoreGroup();
            $defaultStoreId = $defaultStoreGroup->getDefaultStoreId();

            $this->defaultStore = $this->storeManager->getStore($defaultStoreId);
        }

        return $this->defaultStore;
    }

    // ---------------------------------------

    public function getDefaultWebsiteId()
    {
        return (int)$this->getDefaultWebsite()->getId();
    }

    public function getDefaultStoreGroupId()
    {
        return (int)$this->getDefaultStoreGroup()->getId();
    }

    public function getDefaultStoreId()
    {
        return $this->getDefaultStore()->getId();
    }

    //########################################

    public function getStorePath($storeId)
    {
        if ($storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            return (string)__('Admin (Default Values)');
        }

        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return (string)__('Requested store is not found');
        }

        $path = $this->storeManager->getWebsite($store->getWebsiteId())->getName();
        $path .= ' > ' . $this->storeManager->getGroup($store->getStoreGroupId())->getName();
        $path .= ' > ' . $store->getName();

        return $path;
    }

    public function getWebsite($storeId)
    {
        try {
            $store = $this->storeManager->getStore($storeId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $this->storeManager->getWebsite($store->getWebsiteId());
    }

    public function getWebsiteName($storeId)
    {
        $website = $this->getWebsite($storeId);

        return $website ? $website->getName() : '';
    }

    public function getStoreNameById(int $storeId): string
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            $result = $store->getName();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $result = __('Unknown Store (ID: %1)', $storeId);
        }

        return (string)$result;
    }
}
