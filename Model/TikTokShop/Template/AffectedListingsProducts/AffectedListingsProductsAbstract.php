<?php

namespace M2E\TikTokShop\Model\TikTokShop\Template\AffectedListingsProducts;

abstract class AffectedListingsProductsAbstract extends \M2E\TikTokShop\Model\Template\AffectedListingsProductsAbstract
{
    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;
    private \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\TikTokShop\Model\ResourceModel\Listing $listingResource
    ) {
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->listingResource = $listingResource;
    }

    abstract public function getTemplateNick(): string;

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadListingProductCollection(
        array $filters = []
    ): \M2E\TikTokShop\Model\ResourceModel\Product\Collection {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->joinInner(
            ['listing' => $this->listingResource->getMainTable()],
            sprintf(
                'listing_id = `listing`.`%s`',
                \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_ID
            ),
            []
        );

        $collection->getSelect()->where(
            sprintf('`listing`.`%s` = ?', $this->columnTemplateId()),
            $this->getModel()->getId()
        );

        return $collection;
    }

    private function columnTemplateId(): string
    {
        if ($this->getTemplateNick() === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION) {
            return \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_TEMPLATE_DESCRIPTION_ID;
        }

        if ($this->getTemplateNick() === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT) {
            return \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_TEMPLATE_SELLING_FORMAT_ID;
        }

        if ($this->getTemplateNick() === \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION) {
            return \M2E\TikTokShop\Model\ResourceModel\Listing::COLUMN_TEMPLATE_SYNCHRONIZATION_ID;
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown template ' . $this->getTemplateNick());
    }
}
