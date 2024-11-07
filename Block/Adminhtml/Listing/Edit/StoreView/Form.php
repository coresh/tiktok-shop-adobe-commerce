<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Edit\StoreView;

class Form extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\TikTokShop\Model\Listing $listing;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->listing = $data['listing'];
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_store_view_form',
                    'action' => 'javascript:void(0)',
                    'method' => 'post',
                ],
            ]
        );

        $form->addField(
            'attention_text',
            \M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\CustomContainer::class,
            [
                'text' =>
                    <<<HTML
<div class="attention-container">
            <br>
            <p class="attention-text">{$this->__('Switching the store view may initiate an update of products on the channel!')}</p>
            <p class="attention-text">{$this->__('When you switch a store view for a listing, an automatic update of product parameters may be triggered. If product values in the new Store View are different from the current ones (e.g. Price, Description), these changes will be synchronized to the channel based on the rules set in the Synchronization policy.')}</p>
        </div>
HTML
            ]
        );

        $form->addField(
            'id',
            'hidden',
            [
                'name' => 'id',
            ]
        );

        $fieldset = $form->addFieldset(
            'edit_listing_fieldset',
            []
        );

        $fieldset->addField(
            'store_id',
            \M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\StoreSwitcher::class,
            [
                'name' => 'store_id',
                'class' => 'validate-no-empty',
                'label' => __('Store View'),
                'field_extra_attributes' => 'style="margin-top: 20px;"',
            ]
        );

        if ($this->listing->getId()) {
            $form->addValues(
                [
                    'id' => $this->listing->getId(),
                    'store_id' => $this->listing->getStoreId()
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
