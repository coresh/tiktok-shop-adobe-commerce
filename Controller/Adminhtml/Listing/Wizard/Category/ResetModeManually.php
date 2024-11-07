<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing;

class ResetModeManually extends AbstractListing
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

        $manager->resetCategoryIdByProductId($this->getRequestIds('products_id'));

        return $this->getResult();
    }
}
