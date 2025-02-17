<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard;

class CreateUnmanaged extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\Listing\Wizard\Create $createModel;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $listingOtherRepository;
    private \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper;
    private \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\Listing\Wizard\Create $createModel,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $listingOtherRepository,
        \M2E\TikTokShop\Helper\Data\Session $sessionDataHelper,
        \M2E\TikTokShop\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\TikTokShop\Model\Product\Repository $productRepository
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
        $this->createModel = $createModel;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $listingId = (int)$this->getRequest()->getParam('listing_id');
        if (empty($listingId)) {
            $this->getMessageManager()->addError(__('Cannot start Wizard, Listing ID must be provided first.'));

            return $this->_redirect('*/tiktokshop_listing/index');
        }

        $listing = $this->listingRepository->get($listingId);
        $wizard = $this->createModel->process($listing, \M2E\TikTokShop\Model\Listing\Wizard::TYPE_UNMANAGED);
        $manager = $this->wizardManagerFactory->create($wizard);

        $sessionKey = \M2E\TikTokShop\Helper\View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;
        $selectedProducts = $this->sessionDataHelper->getValue($sessionKey);

        $errorsCount = 0;
        foreach ($selectedProducts as $otherListingId) {
            $unmanagedProduct = $this->listingOtherRepository->get((int)$otherListingId);

            if ($this->productRepository->findByListingAndMagentoProductId($listing, $unmanagedProduct->getMagentoProductId())) {
                $errorsCount++;
                continue;
            }

            $wizardProduct = $manager->addUnmanagedProduct($unmanagedProduct);

            if ($wizardProduct === null) {
                $errorsCount++;
            }
        }

        $this->sessionDataHelper->removeValue($sessionKey);

        if ($errorsCount) {
            if (count($selectedProducts) == $errorsCount) {
                $manager->cancel();

                $this->getMessageManager()->addErrorMessage(
                    __(
                        'Products were not moved because they already exist in the selected Listing or do not
                            belong to the channel account or shop of the listing.'
                    )
                );

                return $this->_redirect('*/product_grid/unmanaged');
            }

            $this->getMessageManager()->addErrorMessage(
                __(
                    'Some products were not moved because they already exist in the selected Listing or do not
                    belong to the channel account or shop of the listing.'
                )
            );
        }

        return $this->_redirect('*/listing_wizard/index', ['id' => $wizard->getId()]);
    }
}
