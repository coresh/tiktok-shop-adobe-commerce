<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\Category;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractListing;

class ChooserBlockModeManually extends AbstractListing
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

        $selectedProduct = $this->getRequest()->getParam('products_ids');

        /** @var \M2E\TikTokShop\Model\Listing\Wizard\Product $wizardProduct */
        $wizardProduct = $manager->findProductById((int)$selectedProduct);

        /** @var \M2E\TikTokShop\Block\Adminhtml\Category\CategoryChooser $chooserBlock */
        $chooserBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Category\CategoryChooser::class,
                '',
                [
                    'listing' => $manager->getListing(),
                    'selectedCategory' => $wizardProduct->getCategoryId(),
                ],
            );

        $this->setAjaxContent($chooserBlock->toHtml());

        return $this->getResult();
    }
}
