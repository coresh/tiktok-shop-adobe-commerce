<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard;

class Create extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    use \M2E\TikTokShop\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\Listing\Wizard\Create $createModel;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\Listing\Wizard\Create $createModel
    ) {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->createModel = $createModel;
    }

    public function execute()
    {
        $listingId = (int)$this->getRequest()->getParam('listing_id');
        $type = $this->getRequest()->getParam('type');
        if (empty($listingId) || empty($type)) {
            $this->getMessageManager()->addError(__('Cannot start Wizard, Listing ID must be provided first.'));

            return $this->_redirect('*/tiktokshop_listing/index');
        }

        $listing = $this->listingRepository->get($listingId);

        if (!$listing->getShop()->hasDefaultWarehouse()) {
            $this->addExtendedErrorMessage(
                __(
                    'The Product(s) cannot be added because the default warehouse is not configured.' .
                    ' Please ensure that warehouses are set up for the selected Shop in %channel_title Seller Center' .
                    ' and use <b>"Update Access Data"</b> in %extension_title to synchronize the updates.',
                    [
                        'channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle(),
                        'extension_title' => \M2E\TikTokShop\Helper\Module::getExtensionTitle()
                    ]
                )
            );

            return $this->_redirect('*/tiktokshop_listing/view', ['id' => $listingId]);
        }

        $wizard = $this->createModel->process($listing, $type);

        return $this->redirectToIndex($wizard->getId());
    }
}
