<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Product;

class SaveProductsAndGetInfo extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
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

        $productsIds = $stepData['products_ids'] ?? [];

        $checked = $this->getRequest()->getParam('checked_ids');
        $initial = $this->getRequest()->getParam('initial_checked_ids');

        $checked = explode(',', $checked);
        $initial = explode(',', $initial);

        $initial = array_values(array_unique(array_merge($initial, $checked)));
        $productsIds = array_values(array_unique(array_merge($productsIds, $initial)));

        $productsIds = array_flip($productsIds);

        foreach (array_diff($initial, $checked) as $id) {
            unset($productsIds[$id]);
        }

        $stepData['products_ids'] = array_values(array_filter(array_flip($productsIds)));
        $manager->setStepData(\M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_SELECT_PRODUCTS, $stepData);

        // ---------------------------------------

        $this->_forward('getTreeInfo');
    }
}
