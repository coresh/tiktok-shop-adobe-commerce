<?php

namespace M2E\TikTokShop\Observer\Product\AddUpdate;

abstract class AbstractAddUpdate extends \M2E\TikTokShop\Observer\Product\AbstractProduct
{
    private ?\M2E\TikTokShop\Model\Product\AffectedProduct\Collection $affectedProductCollection = null;
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \M2E\TikTokShop\Model\Magento\ProductFactory $ourMagentoProductFactory,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        parent::__construct(
            $productFactory,
            $ourMagentoProductFactory,
            $helperFactory
        );
        $this->listingProductRepository = $listingProductRepository;
    }

    /**
     * @return bool
     */
    public function canProcess(): bool
    {
        return ((string)$this->getEvent()->getProduct()->getSku()) !== '';
    }

    //########################################

    abstract protected function isAddingProductProcess();

    //########################################

    protected function areThereAffectedItems(): bool
    {
        return !$this->getAffectedProductCollection()->isEmpty();
    }

    // ---------------------------------------

    protected function getAffectedProductCollection(): \M2E\TikTokShop\Model\Product\AffectedProduct\Collection
    {
        if ($this->affectedProductCollection !== null) {
            return $this->affectedProductCollection;
        }

        return $this->affectedProductCollection = $this->listingProductRepository
            ->getListingProductsByMagentoProductId($this->getProductId());
    }
}
