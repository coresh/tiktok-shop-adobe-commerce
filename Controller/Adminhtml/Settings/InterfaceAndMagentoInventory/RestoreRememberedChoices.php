<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Settings\InterfaceAndMagentoInventory;

class RestoreRememberedChoices extends \M2E\TikTokShop\Controller\Adminhtml\AbstractBase
{
    private \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $collection = $this->listingCollectionFactory->create();

        foreach ($collection->getItems() as $listing) {
            $additionalData = $listing->getAdditionalData();

            unset($additionalData['show_settings_step']);
            unset($additionalData['mode_same_category_data']);

            $listing->setAdditionalData($additionalData);
            $this->listingRepository->save($listing);
        }

        $this->setJsonContent(['success' => true]);

        return $this->getResult();
    }
}
