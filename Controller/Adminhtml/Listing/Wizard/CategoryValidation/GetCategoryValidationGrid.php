<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\CategoryValidation;

class GetCategoryValidationGrid extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $runtimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $runtimeStorage,
        $context = null
    ) {
        parent::__construct($context);
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->runtimeStorage = $runtimeStorage;
    }

    public function execute()
    {
        $wizardManager = $this->wizardManagerFactory->createById($this->getWizardIdFromRequest());
        $this->runtimeStorage->setManager($wizardManager);

        $grid = $this->getLayout()
                     ->createBlock(
                         \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\CategoryValidation\Grid::class,
                     );
        $this->setAjaxContent($grid);

        return $this->getResult();
    }
}
