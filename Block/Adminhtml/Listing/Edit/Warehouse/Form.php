<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Edit\Warehouse;

class Form extends \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\TikTokShop\Model\Listing $listing;
    private \M2E\TikTokShop\Model\Warehouse\WarehouseOptionProvider $warehouseOptionProvider;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\TikTokShop\Model\Warehouse\WarehouseOptionProvider $warehouseOptionProvider,
        array $data = []
    ) {
        $this->listing = $data['listing'];
        $this->warehouseOptionProvider = $warehouseOptionProvider;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_warehouse_form',
                    'action' => 'javascript:void(0)',
                    'method' => 'post',
                ],
            ]
        );

        $form->addField(
            'id',
            'hidden',
            [
                'name' => 'id',
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
            <p class="attention-text">
                {$this->__('Select the warehouse where the products added to this Listing are stored.')}
            </p>
            <br>
        </div>
HTML
                ,
            ]
        );

        $fieldset = $form->addFieldset(
            'edit_listing_fieldset',
            []
        );
        $fieldset->addField(
            'warehouse_id',
            self::SELECT,
            [
                'name' => 'warehouse_id',
                'label' => __('Warehouse'),
                'value' => $this->listing->getWarehouseId(),
                'values' => $this->getWarehouses(),
                'field_extra_attributes' => 'style="margin-bottom: 0px"',
            ]
        );

        if ($this->listing->getId()) {
            $form->addValues(
                [
                    'id' => $this->listing->getId(),
                    'warehouse_id' => $this->listing->getWarehouseId(),
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array{
     *    label: string,
     *    value: int
     * }
     */
    private function getWarehouses(): array
    {
        return $this->warehouseOptionProvider->getOptionsByShopId(
            $this->listing->getShopId()
        );
    }
}
