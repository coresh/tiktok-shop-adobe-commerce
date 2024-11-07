<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Product\Category\Settings;

class Edit extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing
{
    private \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository;
    private \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product $listingProductResource,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository,
        \M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();

        $this->listingProductResource = $listingProductResource;
        $this->categoryRepository = $categoryRepository;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        /** @var string[] $listingProductId */
        $listingProductIds = $this->getRequestIds('products_id');
        if (empty($listingProductIds)) {
            return $this->getFailAjaxResult('Invalid product id(s)');
        }

        $shopId = $this->getRequest()->getParam('shop_id');
        if (empty($shopId)) {
            return $this->getFailAjaxResult('Invalid shop id');
        }

        $listing = $this->listingRepository->find((int)$this->getRequest()->getParam('id'));
        if ($listing === null) {
            return $this->getFailAjaxResult('Listing not found');
        }

        $this->uiListingRuntimeStorage->setListing($listing);

        $ids = $this->listingProductResource
            ->getTemplateCategoryIds($listingProductIds, 'template_category_id', true);

        $categories = $this->categoryRepository->getItems($ids);
        if (empty($categories)) {
            return $this->getFailAjaxResult('Categories not found');
        }

        /** @var ?\M2E\TikTokShop\Model\Category\Dictionary $entity */
        $category = count($categories) === 1 ? reset($categories) : null;

        /** @var \M2E\TikTokShop\Block\Adminhtml\Category\CategoryChooser $block */
        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Category\CategoryChooser::class,
            '',
            ['selectedCategory' => $category !== null ? $category->getCategoryId() : null]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }

    private function getFailAjaxResult(string $message): \Magento\Framework\Controller\Result\Raw
    {
        $this->setJsonContent([
            'result' => false,
            'message' => $message,
        ]);

        return $this->getResult();
    }
}
