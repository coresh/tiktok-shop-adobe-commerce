<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Wizard;

class ProductSourceSelect extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer
{
    use WizardTrait;

    public const MODE_PRODUCT = 'product';
    public const MODE_CATEGORY = 'category';

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

        $this->_headerText = __('Add Products');

        $this->prepareButtons(
            [
                'label' => __('Continue'),
                'onclick' => sprintf(
                    "CommonObj.submitForm('%s');",
                    $this->getUrl(
                        '*/listing_wizard_productSource/complete',
                        ['id' => $this->uiWizardRuntimeStorage->getManager()->getWizardId()],
                    ),
                ),
                'class' => 'action-primary forward',
            ],
            $this->uiWizardRuntimeStorage->getManager(),
        );
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', \M2E\TikTokShop\Block\Adminhtml\Listing\Wizard\ProductSource\Form::class);

        return parent::_prepareLayout();
    }
}
