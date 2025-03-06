<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Category;

class Recommended extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractCategory
{
    private \M2E\TikTokShop\Model\Category\Recommended $recommendedCategory;
    private \M2E\TikTokShop\Model\Listing\Wizard\Repository $listingWizardRepository;
    private \M2E\TikTokShop\Model\Magento\Product\CacheFactory $magentoProductFactory;
    private \M2E\TikTokShop\Model\Product\Repository $listingProductRepository;
    private \M2E\TikTokShop\Model\Magento\Product\Cache $magentoProductModel;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Repository $listingProductRepository,
        \M2E\TikTokShop\Model\Category\Recommended $recommendedCategory,
        \M2E\TikTokShop\Model\Listing\Wizard\Repository $listingWizardRepository,
        \M2E\TikTokShop\Model\Magento\Product\CacheFactory $magentoProductFactory
    ) {
        parent::__construct();

        $this->recommendedCategory = $recommendedCategory;
        $this->listingWizardRepository = $listingWizardRepository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->listingProductRepository = $listingProductRepository;
    }

    public function execute()
    {
        $shopId = (int)$this->getRequest()->getParam('shop_id');
        $wizardId = $this->getRequest()->getParam('wizard_id');
        $productId = $this->getRequest()->getParam('product_id');

        $result = [];

        if (empty($shopId)) {
            $this->setJsonContent($result);

            return $this->getResult();
        }

        $this->magentoProductModel = $this->magentoProductFactory->create();
        if (!empty($wizardId)) {
            if (empty($productId)) {
                $magentoProductId = $this->listingWizardRepository->getMagentoProductIdByWizardId((int)$wizardId);

                $result['categories'] = $this->getSearchResult($magentoProductId, $shopId);
            } else {
                $magentoProductId = $this->listingWizardRepository
                    ->getMagentoProductIdByWizardIdAndProductId(
                        (int)$wizardId,
                        (int)$productId
                    );

                $result['categories'] = $this->getSearchResult($magentoProductId, $shopId);
            }
        } else {
            if (!empty($productId)) {
                $listingProduct = $this->listingProductRepository->find((int)$productId);
                $magentoProductId = $listingProduct->getMagentoProductId();

                $result['categories'] = $this->getSearchResult($magentoProductId, $shopId);
            }
        }

        $this->setJsonContent($result);

        return $this->getResult();
    }

    private function getSearchResult(int $magentoProductId, int $shopId): array
    {
        $result = [];

        $magentoProductName = $this->magentoProductModel->getNameByProductId($magentoProductId);
        $searchResult = $this->recommendedCategory->process($shopId, $magentoProductName);

        if (isset($searchResult)) {
            $result = [
                'id' => $searchResult->categoryId,
                'path' => $searchResult->path,
                'is_invite' => $searchResult->isInviteOnly,
                'is_valid' => $searchResult->isValid,
            ];
        }

        return $result;
    }
}
