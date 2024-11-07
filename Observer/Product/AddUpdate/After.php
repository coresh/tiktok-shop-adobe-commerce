<?php

namespace M2E\TikTokShop\Observer\Product\AddUpdate;

use Magento\Catalog\Model\Product\Attribute\Source\Status;

class After extends AbstractAddUpdate
{
    private \Magento\Eav\Model\Config $eavConfig;
    private \Magento\Store\Model\StoreManager $storeManager;
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private \M2E\TikTokShop\Model\Listing\Log\Repository $listingLogRepository;
    private array $listingsProductsChangedAttributes = [];
    private array $attributeAffectOnStoreIdCache = [];

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Listing\Log\Repository $listingLogRepository,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory
    ) {
        parent::__construct(
            $listingProductRepository,
            $productFactory,
            $ourMagentoProductFactory,
            $helperFactory
        );

        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->listingLogService = $listingLogService;
        $this->listingLogRepository = $listingLogRepository;
    }

    public function beforeProcess(): void
    {
        parent::beforeProcess();

        if (!$this->isProxyExist()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                'Before proxy should be defined earlier than after Action is performed.'
            );
        }

        if ($this->getProductId() <= 0) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Product ID should be defined for "after save" event.');
        }

        $this->reloadProduct();
    }

    // ---------------------------------------

    protected function process(): void
    {
        if ($this->isAddingProductProcess()) {
            return;
        }

        $this->updateProductsNamesInLogs();

        if (!$this->areThereAffectedItems()) {
            return;
        }

        $this->performStatusChanges();
        $this->performPriceChanges();
        $this->performSpecialPriceChanges();
        $this->performSpecialPriceFromDateChanges();
        $this->performSpecialPriceToDateChanges();
        $this->performTierPriceChanges();
        $this->performTrackingAttributesChanges();
        $this->performDefaultQtyChanges();

        $this->addListingProductInstructions();
    }

    private function updateProductsNamesInLogs()
    {
        if (!$this->isAdminDefaultStoreId()) {
            return;
        }

        $name = $this->getProduct()->getName();

        if ($this->getProxy()->getData('name') === $name) {
            return;
        }

        $this->listingLogRepository->updateProductTitle($this->getProductId(), $name);
    }

    private function performStatusChanges()
    {
        $oldValue = (int)$this->getProxy()->getData('status');
        $newValue = (int)$this->getProduct()->getStatus();

        if ($oldValue == $newValue) {
            return;
        }

        $oldValue = ($oldValue == Status::STATUS_ENABLED) ? 'Enabled' : 'Disabled';
        $newValue = ($newValue == Status::STATUS_ENABLED) ? 'Enabled' : 'Disabled';

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $listingProductStoreId = $affectedProduct->getProduct()->getListing()->getStoreId();

            if (!$this->isAttributeAffectOnStoreId('status', $listingProductStoreId)) {
                continue;
            }

            $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId()][] = 'status';

            $this->logListingProductMessage(
                $affectedProduct,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_STATUS,
                $oldValue,
                $newValue
            );
        }
    }

    private function performPriceChanges()
    {
        $oldValue = round((float)$this->getProxy()->getData('price'), 2);
        $newValue = round((float)$this->getProduct()->getPrice(), 2);

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId()][] = 'price';

            $this->logListingProductMessage(
                $affectedProduct,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_PRICE,
                $oldValue,
                $newValue
            );
        }
    }

    private function performSpecialPriceChanges()
    {
        $oldValue = round((float)$this->getProxy()->getData('special_price'), 2);
        $newValue = round((float)$this->getProduct()->getSpecialPrice(), 2);

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId()][] = 'special_price';

            $this->logListingProductMessage(
                $affectedProduct,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE,
                $oldValue,
                $newValue
            );
        }
    }

    private function performSpecialPriceFromDateChanges()
    {
        $oldValue = $this->getProxy()->getData('special_price_from_date');
        $newValue = $this->getProduct()->getSpecialFromDate();

        if ($oldValue == $newValue) {
            return;
        }

        ($oldValue === null || $oldValue === false || $oldValue == '') && $oldValue = 'None';
        ($newValue === null || $newValue === false || $newValue == '') && $newValue = 'None';

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId(
            )][] = 'special_price_from_date';

            $this->logListingProductMessage(
                $affectedProduct,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE,
                $oldValue,
                $newValue
            );
        }
    }

    private function performSpecialPriceToDateChanges()
    {
        $oldValue = $this->getProxy()->getData('special_price_to_date');
        $newValue = $this->getProduct()->getSpecialToDate();

        if ($oldValue == $newValue) {
            return;
        }

        ($oldValue === null || $oldValue === false || $oldValue == '') && $oldValue = 'None';
        ($newValue === null || $newValue === false || $newValue == '') && $newValue = 'None';

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId(
            )][] = 'special_price_to_date';

            $this->logListingProductMessage(
                $affectedProduct,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE,
                $oldValue,
                $newValue
            );
        }
    }

    private function performTierPriceChanges()
    {
        $oldValue = $this->getProxy()->getData('tier_price');
        $newValue = $this->getProduct()->getTierPrice();

        if ($oldValue == $newValue) {
            return;
        }

        $oldValue = $this->convertTierPriceForLog($oldValue);
        $newValue = $this->convertTierPriceForLog($newValue);

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId()][] = 'tier_price';

            $this->logListingProductMessage(
                $affectedProduct,
                \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_TIER_PRICE,
                $oldValue,
                $newValue
            );
        }
    }

    // ---------------------------------------

    private function performTrackingAttributesChanges()
    {
        foreach ($this->getProxy()->getAttributes() as $attributeCode => $attributeValue) {
            $oldValue = $attributeValue;
            $newValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);

            if ($oldValue == $newValue) {
                continue;
            }

            foreach ($this->getAffectedListingsProductsByTrackingAttribute($attributeCode) as $affectedProduct) {
                if (
                    !$this->isAttributeAffectOnStoreId(
                        $attributeCode,
                        $affectedProduct->getProduct()->getListing()->getStoreId()
                    )
                ) {
                    continue;
                }

                $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId()][] = $attributeCode;

                $this->logListingProductMessage(
                    $affectedProduct,
                    \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_CUSTOM_ATTRIBUTE,
                    $oldValue,
                    $newValue,
                    'for attribute "' . $attributeCode . '"'
                );
            }
        }
    }

    // ---------------------------------------

    private function performDefaultQtyChanges()
    {
        if (!$this->getHelper('Magento_Product')->isGroupedType($this->getProduct()->getTypeId())) {
            return;
        }

        $values = $this->getProxy()->getData('default_qty');
        foreach ($this->getProduct()->getTypeInstance()->getAssociatedProducts($this->getProduct()) as $childProduct) {
            $sku = $childProduct->getSku();
            $newValue = (int)$childProduct->getQty();
            $oldValue = isset($values[$sku]) ? (int)$values[$sku] : 0;

            unset($values[$sku]);
            if ($oldValue == $newValue) {
                continue;
            }

            foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
                $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId()][] = 'qty';

                $this->logListingProductMessage(
                    $affectedProduct,
                    \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
                    $oldValue,
                    $newValue,
                    "SKU $sku: Default QTY was changed."
                );
            }
        }

        //----------------------------------------

        foreach ($values as $sku => $defaultQty) {
            foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
                $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId()][] = 'qty';

                $this->logListingProductMessage(
                    $affectedProduct,
                    \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
                    $defaultQty,
                    0,
                    "SKU $sku: was removed from the Product Set."
                );
            }
        }
    }

    // ---------------------------------------

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function addListingProductInstructions()
    {
        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $affectedProduct->getProduct(),
                $affectedProduct->getProduct()->getDescriptionTemplate()
            );
            if (
                $changedAttributes = $this->listingsProductsChangedAttributes[$affectedProduct->getProduct()->getId(
                )] ?? null
            ) {
                $changeAttributeTracker->addInstructionsByChangedAttributes($changedAttributes);
            }
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }

    protected function isAddingProductProcess()
    {
        return $this->getProxy()->getProductId() <= 0 && $this->getProductId() > 0;
    }

    // ---------------------------------------

    private function isProxyExist()
    {
        $key = $this->getProductId() . '_' . $this->getStoreId();
        if (isset(\M2E\TikTokShop\Observer\Product\AddUpdate\Before::$proxyStorage[$key])) {
            return true;
        }

        $key = $this
            ->getEvent()
            ->getProduct()
            ->getData(\M2E\TikTokShop\Observer\Product\AddUpdate\Before::BEFORE_EVENT_KEY);

        return isset(\M2E\TikTokShop\Observer\Product\AddUpdate\Before::$proxyStorage[$key]);
    }

    /**
     * @return \M2E\TikTokShop\Observer\Product\AddUpdate\Before\Proxy
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getProxy()
    {
        if (!$this->isProxyExist()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                'Before proxy should be defined earlier than after Action is performed.'
            );
        }

        $key = $this->getProductId() . '_' . $this->getStoreId();
        if (isset(\M2E\TikTokShop\Observer\Product\AddUpdate\Before::$proxyStorage[$key])) {
            return \M2E\TikTokShop\Observer\Product\AddUpdate\Before::$proxyStorage[$key];
        }

        $key = $this
            ->getEvent()
            ->getProduct()
            ->getData(\M2E\TikTokShop\Observer\Product\AddUpdate\Before::BEFORE_EVENT_KEY);

        return \M2E\TikTokShop\Observer\Product\AddUpdate\Before::$proxyStorage[$key];
    }

    private function isAttributeAffectOnStoreId($attributeCode, $onStoreId)
    {
        $cacheKey = $attributeCode . '_' . $onStoreId;

        if (isset($this->attributeAffectOnStoreIdCache[$cacheKey])) {
            return $this->attributeAffectOnStoreIdCache[$cacheKey];
        }

        $attributeInstance = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

        if (!($attributeInstance instanceof \Magento\Catalog\Model\ResourceModel\Eav\Attribute)) {
            return $this->attributeAffectOnStoreIdCache[$cacheKey] = false;
        }

        $attributeScope = (int)$attributeInstance->getData('is_global');

        if (
            $attributeScope == \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL ||
            $this->getStoreId() == $onStoreId
        ) {
            return $this->attributeAffectOnStoreIdCache[$cacheKey] = true;
        }

        if ($this->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productFactory->create();
            $product->setStoreId($onStoreId);
            $product->load($this->getProductId());

            $scopeOverridden = $this->objectManager
                ->create(\Magento\Catalog\Model\Attribute\ScopeOverriddenValue::class);
            $isExistsValueForStore = $scopeOverridden->containsValue(
                \Magento\Catalog\Api\Data\ProductInterface::class,
                $product,
                $attributeCode,
                $onStoreId
            );

            return $this->attributeAffectOnStoreIdCache[$cacheKey] = !$isExistsValueForStore;
        }

        if ($attributeScope == \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE) {
            return $this->attributeAffectOnStoreIdCache[$cacheKey] = false;
        }

        $affectedStoreIds = $this->storeManager->getStore($this->getStoreId())->getWebsite()->getStoreIds();
        $affectedStoreIds = array_map('intval', array_values(array_unique($affectedStoreIds)));

        return $this->attributeAffectOnStoreIdCache[$cacheKey] = in_array($onStoreId, $affectedStoreIds);
    }

    /**
     * @param $attributeCode
     *
     * @return \M2E\TikTokShop\Model\Product\AffectedProduct\Product[]
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function getAffectedListingsProductsByTrackingAttribute($attributeCode): array
    {
        $result = [];

        foreach ($this->getAffectedProductCollection()->getProducts() as $affectedProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $affectedProduct->getProduct(),
                $affectedProduct->getProduct()->getDescriptionTemplate()
            );
            if (in_array($attributeCode, $changeAttributeTracker->getTrackingAttributes())) {
                $result[] = $affectedProduct;
            }
        }

        return $result;
    }

    private function convertTierPriceForLog($tierPrice): string
    {
        if (empty($tierPrice) || !is_array($tierPrice)) {
            return 'None';
        }

        $result = [];
        foreach ($tierPrice as $tierPriceData) {
            $result[] = sprintf(
                "[price = %s, qty = %s]",
                $tierPriceData["website_price"],
                $tierPriceData["price_qty"]
            );
        }

        return implode(",", $result);
    }

    private function logListingProductMessage(
        \M2E\TikTokShop\Model\Product\AffectedProduct\Product $affectedProduct,
        int $action,
        $oldValue,
        $newValue,
        $messagePostfix = ''
    ): void {
        $oldValue = strlen($oldValue) > 150 ? substr($oldValue, 0, 150) . ' ...' : $oldValue;
        $newValue = strlen($newValue) > 150 ? substr($newValue, 0, 150) . ' ...' : $newValue;

        $messagePostfix = trim(trim($messagePostfix), '.');
        if (!empty($messagePostfix)) {
            $messagePostfix = ' ' . $messagePostfix;
        }

        if ($affectedProduct->isAffectedVariant()) {
            $description = \M2E\TikTokShop\Helper\Module\Log::encodeDescription(
                'SKU: "%sku%"; From [%from%] to [%to%]' . $messagePostfix . '.',
                [
                    '!sku' => $affectedProduct->getVariant()->getSku(),
                    '!from' => $oldValue,
                    '!to' => $newValue,
                ]
            );
        } else {
            $description = \M2E\TikTokShop\Helper\Module\Log::encodeDescription(
                'From [%from%] to [%to%]' . $messagePostfix . '.',
                ['!from' => $oldValue, '!to' => $newValue]
            );
        }

        $this->listingLogService->addProduct(
            $affectedProduct->getProduct(),
            \M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION,
            $action,
            null,
            $description,
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
