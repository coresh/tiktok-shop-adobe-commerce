<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Category;

class Delete extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository;
    private \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository
    ) {
        parent::__construct();

        $this->categoryRepository = $categoryRepository;
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingRepository = $listingRepository;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function execute()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->getMessageManager()->addError(__('Please select Item(s) to remove.'));
            $this->_redirect('*/*/index');

            return;
        }

        $categories = $this->categoryRepository->getItems($ids);

        $locked = 0;
        $deleted = 0;
        $idsOfDeletedCategories = [];

        foreach ($categories as $category) {
            if ($category->isLocked()) {
                $locked++;
                continue;
            }

            $this->categoryRepository->delete($category);

            $idsOfDeletedCategories[] = $category->getId();

            $deleted++;
        }

        if ($idsOfDeletedCategories) {
            $this->unsetCategoryData($idsOfDeletedCategories);
        }

        $errorMessage = __('%s record(s) were deleted.', ['s' => $deleted]);
        if ($deleted) {
            $this->getMessageManager()->addSuccess($errorMessage);
        }

        $errorMessage = __(
            '[%count] Category cannot be removed until it’s unassigned from the existing products.',
            ['count' => $locked],
        );

        if ($locked) {
            $this->getMessageManager()->addError($errorMessage);
        }

        $this->_redirect('*/tiktokshop_template_category/index');
    }

    /**
     * @param array $ids
     *
     * @return void
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function unsetCategoryData(array $ids): void
    {
        $collection = $this->listingCollectionFactory->create();
        $collection
            ->addFieldToSelect(['id', 'additional_data'])
            ->addFieldToFilter('additional_data', ['like' => '%mode_same_category_data%']);

        foreach ($collection->getItems() as $listing) {
            $additionalData = $listing->getAdditionalData();

            if (empty($additionalData['mode_same_category_data'])) {
                continue;
            }

            $save = false;

            foreach ($additionalData['mode_same_category_data'] as $templateData) {
                if (in_array($templateData['template_id'], $ids, true)) {
                    unset($additionalData['mode_same_category_data']);
                    $save = true;
                }
            }

            if ($save) {
                $listing->setAdditionalData($additionalData);
                $this->listingRepository->save($listing);
            }
        }
    }
}
