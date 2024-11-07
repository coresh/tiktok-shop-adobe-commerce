<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Product;

class RemoveProductsByCategory extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);
        $stepData = $manager->getStepData(\M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS);

        $selectedProductsIds = $stepData['products_ids'] ?? [];
        $categoriesIds = $this->getRequestIds();

        if (empty($selectedProductsIds)) {
            return;
        }
        /** @var \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category\Add\Tree::class $treeBlock */
        $treeBlock = $this->getLayout()
                          ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category\Add\Tree::class);
        $treeBlock->setSelectedIds($selectedProductsIds);

        $productsForEachCategory = $treeBlock->getProductsForEachCategory();

        $products = [];
        foreach ($categoriesIds as $categoryId) {
            $products = array_merge($products, $productsForEachCategory[$categoryId]);
        }

        $stepData['products_ids'] = array_diff($selectedProductsIds, $products);
        $manager->setStepData(\M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS, $stepData);
    }
}
