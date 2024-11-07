<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category;

class Manually extends \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    use \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private array $categoriesData;
    private \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        array $categoriesData,
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->categoriesData = $categoriesData;
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingCategoryManually');

        $this->_headerText = $this->__('Set Category (manually)');

        $this->prepareButtons(
            [
                'id' => 'listing_category_continue_btn',
                'class' => 'action-primary forward',
                'label' => __('Continue'),
                'onclick' => 'ListingWizardCategoryModeManuallyGridObj.completeCategoriesDataStep(1, 0);',
            ],
            $this->uiWizardRuntimeStorage->getManager(),
        );
    }

    protected function _prepareLayout()
    {
        $gridBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category\ModeManually\Grid::class,
                '',
                [
                    'categoriesData' => $this->categoriesData,
                ],
            );

        $this->setChild('grid', $gridBlock);

        return parent::_prepareLayout();
    }
}
