<?php

namespace M2E\TikTokShop\Plugin\MSI\Magento\Inventory\Model\StockSourceLink\Command;

use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

class Save extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    /** @var \M2E\TikTokShop\Model\MSI\AffectedProducts */
    private $msiAffectedProducts;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var \M2E\TikTokShop\PublicServices\Product\SqlChange */
    private $publicService;

    /** @var \Magento\InventoryApi\Api\GetStockSourceLinksInterface */
    private $getStockSourceLinks;

    /** @var \Magento\InventoryApi\Api\StockRepositoryInterface */
    private $stockRepository;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \M2E\TikTokShop\Model\MSI\AffectedProducts $msiAffectedProducts,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \M2E\TikTokShop\PublicServices\Product\SqlChange $publicService,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($helperFactory);
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->publicService = $publicService;
        $this->getStockSourceLinks = $objectManager->get(\Magento\InventoryApi\Api\GetStockSourceLinksInterface::class);
        $this->stockRepository = $objectManager->get(\Magento\InventoryApi\Api\StockRepositoryInterface::class);
        $this->listingLogService = $listingLogService;
        $this->productRepository = $productRepository;
    }

    //########################################

    /**
     * @param $interceptor
     * @param \Closure $callback
     * @param array ...$arguments
     *
     * @return mixed
     */
    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    /**
     * @param $interceptor
     * @param $result
     * @param array ...$arguments
     *
     * @return mixed
     */
    public function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        /** @var \Magento\Inventory\Model\StockSourceLink[] $stockSourceLinks */
        $stockSourceLinks = $arguments[0];
        $stockId = reset($stockSourceLinks)->getStockId();

        $afterSources = [];
        foreach ($stockSourceLinks as $link) {
            $afterSources[] = $link->getSourceCode();
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->create();

        $beforeSources = [];
        foreach ($this->getStockSourceLinks->execute($searchCriteria)->getItems() as $link) {
            $beforeSources[] = $link->getSourceCode();
        }

        $result = $callback(...$arguments);

        sort($beforeSources) && sort($afterSources);
        if ($beforeSources === $afterSources) {
            return $result;
        }

        foreach ($this->msiAffectedProducts->getAffectedListingsByStock($stockId) as $listing) {
            foreach ($this->productRepository->findMagentoProductIdsByListingId($listing->getId()) as $productId) {
                $this->publicService->markQtyWasChanged($productId);
            }
            $this->logListingMessage($listing, $this->stockRepository->get($stockId));
        }
        $this->publicService->applyChanges();

        return $result;
    }

    //########################################

    private function logListingMessage(
        \M2E\TikTokShop\Model\Listing $listing,
        \Magento\InventoryApi\Api\Data\StockInterface $stock
    ): void {
        $this->listingLogService->addListing(
            $listing,
            \M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_UNKNOWN,
            null,
            \M2E\TikTokShop\Helper\Module\Log::encodeDescription(
                'Source set was changed in the "%stock%" Stock used for M2E TikTok Shop Connect Listing.',
                ['!stock' => $stock->getName()]
            ),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO
        );
    }
}
