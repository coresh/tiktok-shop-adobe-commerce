<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\Category;

class Same extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    use \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('listingCategoryChooser');

        $this->prepareButtons(
            [
                'label' => __('Continue'),
                'class' => 'action-primary forward',
                'onclick' => sprintf(
                    "TikTokShopListingCategoryObj.modeSameSubmitData('%s')",
                    $this->getUrl(
                        '*/listing_wizard_category/assignModeSame',
                        ['id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId()],
                    ),
                ),
            ],
            $this->uiWizardRuntimeStorage->getManager(),
        );

        $this->_headerText = __('Categories');
    }

    protected function _beforeToHtml()
    {
        $this->js->add(
            <<<JS
 require([
    'TikTokShop/Listing/Wizard/Category'
], function() {
    window.TikTokShopListingCategoryObj = new TikTokShopListingCategory(null);
});
JS,
        );

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        /** @var \M2E\TikTokShop\Block\Adminhtml\Category\CategoryChooser $chooserBlock */
        $chooserBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\TikTokShop\Block\Adminhtml\Category\CategoryChooser::class,
                '',
                ['selectedCategory' => null],
            );

        return parent::_toHtml()
            . $chooserBlock->toHtml();
    }
}
