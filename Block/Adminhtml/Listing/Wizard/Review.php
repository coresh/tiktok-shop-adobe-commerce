<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard;

class Review extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
{
    use ReviewTrait;

    private \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
        parent::__construct($context, $data);
    }

    public function _construct(): void
    {
        parent::_construct();

        $this->setId('listingProductReview');
        $this->setTemplate('listing/wizard/review.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->addGoToListingButton();
        $this->addListActionButton();
    }

    private function addListActionButton(): void
    {
        $buttonBlock = $this->getLayout()
                            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                            ->setData(
                                [
                                    'label' => __('List Added Products Now'),
                                    'onclick' => 'setLocation(\'' . $this->generateCompleteUrl(true, $this->generateListingViewUrl(true)) . '\');',
                                    'class' => 'primary',
                                ],
                            );

        $this->setChild('save_and_list', $buttonBlock);
    }
}
