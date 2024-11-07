<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other;

class MappingService
{
    private \Magento\Catalog\Model\ProductFactory $productFactory;
    private \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;
    private \M2E\TikTokShop\Model\Listing\Other\Repository $listingOtherRepository;

    public function __construct(
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\TikTokShop\Model\Listing\Other\Repository $listingOtherRepository
    ) {
        $this->productFactory = $productFactory;
        $this->magentoProductHelper = $magentoProductHelper;
        $this->listingOtherRepository = $listingOtherRepository;
    }

    /**
     * @param \M2E\TikTokShop\Model\Listing\Other[] $otherListings
     *
     * @return bool
     * @throws \M2E\TikTokShop\Model\Exception
     */

    public function autoMapOtherListingsProducts(array $otherListings): bool
    {
        $otherListingsFiltered = [];
        foreach ($otherListings as $otherListing) {
            if ($otherListing->hasMagentoProductId()) {
                continue;
            }

            $otherListingsFiltered[] = $otherListing;
        }

        if (count($otherListingsFiltered) <= 0) {
            return false;
        }

        $result = true;
        foreach ($otherListingsFiltered as $otherListing) {
            if (!$this->autoMapOtherListingProduct($otherListing)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function autoMapOtherListingProduct(\M2E\TikTokShop\Model\Listing\Other $otherListing): bool
    {
        if ($otherListing->hasMagentoProductId()) {
            return false;
        }

        if (!$otherListing->getAccount()->getUnmanagedListingSettings()->isMappingEnabled()) {
            return false;
        }

        $magentoProductId = $this->findMagentoProductId($otherListing);
        if ($magentoProductId === null) {
            return false;
        }

        $otherListing->mapToMagentoProduct($magentoProductId);

        $this->listingOtherRepository->save($otherListing);

        return true;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function findMagentoProductId(\M2E\TikTokShop\Model\Listing\Other $otherListing): ?int
    {
        $mappingTypes = $otherListing->getAccount()->getUnmanagedListingSettings()->getMappingTypesByPriority();
        foreach ($mappingTypes as $type) {
            $magentoProductId = null;

            if ($type === \M2E\TikTokShop\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_SKU) {
                $magentoProductId = $this->getSkuMappedMagentoProductId($otherListing);
            }

            if ($type === \M2E\TikTokShop\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_TITLE) {
                $magentoProductId = $this->getTitleMappedMagentoProductId($otherListing);
            }

            if ($type === \M2E\TikTokShop\Model\Account\Settings\UnmanagedListings::MAPPING_TYPE_BY_ITEM_ID) {
                $magentoProductId = $this->getItemIdMappedMagentoProductId($otherListing);
            }

            if ($magentoProductId === null) {
                continue;
            }

            return $magentoProductId;
        }

        return null;
    }

    // ----------------------------------------

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function getSkuMappedMagentoProductId(\M2E\TikTokShop\Model\Listing\Other $otherListing): ?int
    {
        $temp = $otherListing->getSku();

        if (empty($temp)) {
            return null;
        }

        $settings = $otherListing->getAccount()->getUnmanagedListingSettings();

        if ($settings->isMappingBySkuModeByProductId()) {
            $productId = trim($otherListing->getSku());

            if (!ctype_digit($productId) || (int)$productId <= 0) {
                return null;
            }

            $product = $this->productFactory->create()->load($productId);

            if (
                $product->getId()
                && $this->isMagentoProductTypeAllowed($product->getTypeId())
            ) {
                return (int)$product->getId();
            }

            return null;
        }

        $attributeCode = null;

        if ($settings->isMappingBySkuModeBySku()) {
            $attributeCode = 'sku';
        }

        if ($settings->isMappingBySkuModeByAttribute()) {
            $attributeCode = $settings->getMappingAttributeBySku();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getRelatedStoreId();
        $attributeValue = trim($otherListing->getSku());

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if (
            $productObj
            && $productObj->getId()
            && $this->isMagentoProductTypeAllowed($productObj->getTypeId())
        ) {
            return (int)$productObj->getId();
        }

        return null;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function getTitleMappedMagentoProductId(\M2E\TikTokShop\Model\Listing\Other $otherListing): ?int
    {
        $temp = $otherListing->getTitle();

        if (empty($temp)) {
            return null;
        }

        $settings = $otherListing->getAccount()->getUnmanagedListingSettings();

        $attributeCode = null;

        if ($settings->isMappingByTitleModeByProductName()) {
            $attributeCode = 'name';
        }

        if ($settings->isMappingByTitleModeByAttribute()) {
            $attributeCode = $settings->getMappingAttributeByTitle();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getRelatedStoreId();
        $attributeValue = trim($otherListing->getTitle());

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if (
            $productObj
            && $productObj->getId()
            && $this->isMagentoProductTypeAllowed($productObj->getTypeId())
        ) {
            return (int)$productObj->getId();
        }

        return null;
    }

    private function getItemIdMappedMagentoProductId(\M2E\TikTokShop\Model\Listing\Other $otherListing): ?int
    {
        $temp = $otherListing->getProductId();

        if (empty($temp)) {
            return null;
        }

        $settings = $otherListing->getAccount()->getUnmanagedListingSettings();

        $attributeCode = null;

        if ($settings->isMappingByItemIdEnabled()) {
            $attributeCode = $settings->getMappingAttributeByItemId();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getRelatedStoreId();
        $attributeValue = $otherListing->getProductId();

        $productObj = $this->productFactory->create()->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if (
            $productObj
            && $productObj->getId()
        ) {
            return (int)$productObj->getId();
        }

        return null;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception
     */
    private function isMagentoProductTypeAllowed($type): bool
    {
        $knownTypes = $this->magentoProductHelper->getOriginKnownTypes();

        return in_array($type, $knownTypes);
    }
}
