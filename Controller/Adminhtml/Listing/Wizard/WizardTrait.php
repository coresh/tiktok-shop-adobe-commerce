<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard;

trait WizardTrait
{
    private function redirectToIndex($id): \Magento\Framework\App\ResponseInterface
    {
        return $this->_redirect('*/listing_wizard/index', [
            'id' => $id,
        ]);
    }

    private function loadManagerToRuntime(
        \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $managerFactory,
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $runtimeStorage
    ): void {
        $manager = $managerFactory->createById($this->getWizardIdFromRequest());
        $runtimeStorage->setManager($manager);
    }

    private function getWizardIdFromRequest(): int
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (empty($id)) {
            throw new \M2E\TikTokShop\Model\Listing\Wizard\Exception\NotFoundException('Params not valid.');
        }

        return $id;
    }
}
