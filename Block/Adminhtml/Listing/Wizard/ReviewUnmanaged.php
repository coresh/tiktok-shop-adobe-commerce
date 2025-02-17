<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard;

class ReviewUnmanaged extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer
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
        $this->setTemplate('listing/wizard/review_unmanaged.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->addGoToListingButton();
        $this->addGoToUnmanagedButton();
    }

    private function addGoToUnmanagedButton(): void
    {
        $accountId = $this->uiWizardRuntimeStorage->getManager()->getListing()->getAccountId();

        $buttonBlock = $this->getLayout()
                            ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Button::class)
                            ->setData(
                                [
                                    'label' => __('Back to Unmanaged Items'),
                                    'onclick' => 'setLocation(\'' . $this->generateCompleteUrl(false, $this->getUrl('*/product_grid/unmanaged', ['account' => $accountId])) . '\');',
                                    'class' => 'primary go',
                                ],
                            );

        $this->setChild('go_to_unmanaged', $buttonBlock);
    }
}
