<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory;

class AssignModeSame extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\TikTokShop\Model\Category\Dictionary\Repository $categoryRepository,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->categoryRepository = $categoryRepository;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $id = $this->getWizardIdFromRequest();
        $manager = $this->wizardManagerFactory->createById($id);

        $categoryData = [];
        if ($param = $this->getRequest()->getParam('category_data')) {
            $categoryData = json_decode($param, true);
        }

        $dictionaryId = (int)($categoryData['dictionaryId'] ?? 0);
        if (empty($dictionaryId)) {
            return $this->redirectToIndex($id);
        }

        $category = $this->categoryRepository->get($dictionaryId);

        $manager->setProductsCategoryIdSame($category->getId());

        $manager->completeStep(StepDeclarationCollectionFactory::STEP_SELECT_CATEGORY);

        return $this->redirectToIndex($id);
    }
}
