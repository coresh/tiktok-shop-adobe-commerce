<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\CategoryValidation;

use M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer;
use M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage as WizardRuntimeStorage;
use M2E\TikTokShop\Model\Listing\Ui\RuntimeStorage as ListingRuntimeStorage;
use M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget;

class View extends AbstractContainer
{
    use \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private ListingRuntimeStorage $uiListingRuntimeStorage;

    private WizardRuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        ListingRuntimeStorage $uiListingRuntimeStorage,
        WizardRuntimeStorage $uiWizardRuntimeStorage,
        Widget $context,
        array $data = []
    ) {
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;

        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('tikTokShopListingAttributesValidation');

        $resetCategoryValidationData = $this->getUrl(
            '*/listing_wizard_categoryValidation/resetCategoryValidationData',
            [
                'id' => $this->getWizardIdFromRequest()
            ]
        );

        $this->addButton(
            'export_attributes',
            [
                'label' => __('Validate Attributes'),
                'class' => 'action-primary',
                'onclick' => "setLocation('$resetCategoryValidationData');",
            ]
        );

        $continueUrl = $this->getUrl(
            '*/listing_wizard_categoryValidation/complete',
            [
                'id' => $this->getWizardIdFromRequest()
            ]
        );

        $this->prepareButtons(
            [
                'class' => 'action-primary forward',
                'label' => __('Continue'),
                'onclick' => "setLocation('$continueUrl');",
            ],
            $this->uiWizardRuntimeStorage->getManager()
        );
    }

    protected function _prepareLayout()
    {
        $gridBlock = $this
            ->getLayout()
            ->createBlock(Grid::class);
        $this->setChild('grid', $gridBlock);

        $headerBlock = $this
            ->getLayout()
            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Listing\View\Header::class, '', [
                'data' => ['listing' => $this->uiListingRuntimeStorage->getListing()],
            ]);
        $this->setChild('listing_header', $headerBlock);

        return parent::_prepareLayout();
    }
}
