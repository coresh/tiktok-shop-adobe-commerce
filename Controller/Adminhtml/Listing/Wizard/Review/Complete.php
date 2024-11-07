<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Review;

class Complete extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;
    private \M2E\TikTokShop\Model\Listing\Wizard\CompleteProcessor $completeProcessor;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\TikTokShop\Model\Listing\Wizard\CompleteProcessor $completeProcessor,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper
    ) {
        parent::__construct();

        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->sessionHelper = $sessionHelper;
        $this->completeProcessor = $completeProcessor;
    }

    public function execute()
    {
        $backUrl = $this->getRequest()->getParam('next_url');
        if (empty($backUrl) || !($backUrl = base64_decode($backUrl))) {
            return $this->redirectToIndex($this->getWizardIdFromRequest());
        }

        $id = $this->getWizardIdFromRequest();
        $wizardManager = $this->wizardManagerFactory->createById($id);

        $listingProducts = $this->completeProcessor->process($wizardManager);

        $wizardManager->completeStep(
            \M2E\TikTokShop\Model\Listing\Wizard\StepDeclarationCollectionFactory::STEP_REVIEW,
        );
        $wizardManager->setProductCountTotal(count($listingProducts));

        if ($this->getRequest()->getParam('do_list')) {
            // temporary
            $ids = array_map(static function ($product) {
                return $product->getId();
            }, $listingProducts);
            $this->sessionHelper->setValue('added_products_ids', $ids);
        }

        return $this->_redirect($backUrl);
    }
}
