<?php

namespace M2E\TikTokShop\Plugin\MSI\Magento\Inventory\Model\SourceItem\Command;

class Delete extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    private \M2E\TikTokShop\Model\MSI\AffectedProducts $msiAffectedProducts;
    private \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\MSI\AffectedProducts $msiAffectedProducts,
        \M2E\TikTokShop\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory
    ) {
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->listingLogService = $listingLogService;
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    protected function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        /** @var \Magento\InventoryApi\Api\Data\SourceItemInterface[] $sourceItems */
        $sourceItems = $arguments[0];

        $result = $callback(...$arguments);

        foreach ($sourceItems as $sourceItem) {
            $affectedProductCollection = $this->msiAffectedProducts->getAffectedVariantSkuBySourceAndSku(
                $sourceItem->getSourceCode(),
                $sourceItem->getSku()
            );

            if ($affectedProductCollection->isEmpty()) {
                continue;
            }

            $this->addListingProductInstructions($affectedProductCollection);

            foreach ($affectedProductCollection->getProducts() as $product) {
                $this->logListingProductMessage($product, $sourceItem);
            }
        }

        return $result;
    }

    private function logListingProductMessage(
        \M2E\TikTokShop\Model\Product\AffectedProduct\Product $affectedProduct,
        \Magento\InventoryApi\Api\Data\SourceItemInterface $sourceItem
    ): void {
        $this->listingLogService->addProduct(
            $affectedProduct->getProduct(),
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
            null,
            \M2E\TikTokShop\Helper\Module\Log::encodeDescription(
                'The "%source%" Source was unassigned from product.',
                ['!source' => $sourceItem->getSourceCode()]
            ),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function addListingProductInstructions(
        \M2E\TikTokShop\Model\Product\AffectedProduct\Collection $affectedProductCollection
    ): void {
        foreach ($affectedProductCollection->getProducts() as $affectedProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $affectedProduct->getProduct(),
                $affectedProduct->getProduct()->getDescriptionTemplate()
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }
}
