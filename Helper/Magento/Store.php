<?php

namespace M2E\TikTokShop\Helper\Magento;

class Store
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
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
