<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard;

class Cancel extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
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

        $wizardManager = $this->wizardManagerFactory->createById($id);

        $wizardManager->cancel();

        if ($wizardManager->isWizardTypeGeneral()) {
            return $this->_redirect('*/tiktokshop_listing/view', ['id' => $wizardManager->getListing()->getId()]);
        }

        if ($wizardManager->isWizardTypeUnmanaged()) {
            return $this->_redirect('*/product_grid/unmanaged');
        }

        return $this->_redirect('*/*/index');
    }
}
