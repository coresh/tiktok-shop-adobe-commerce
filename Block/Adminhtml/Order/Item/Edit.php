<?php

namespace M2E\TikTokShop\Block\Adminhtml\Order\Item;

use M2E\TikTokShop\Block\Adminhtml\Magento\AbstractContainer;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Order\Item\Edit
 */
class Edit extends AbstractContainer
{
    /** @var \M2E\TikTokShop\Helper\Data */
    private $dataHelper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\TikTokShop\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    protected function _prepareLayout()
    {
        $this->jsUrl->addUrls($this->dataHelper->getControllerActions('Order'));
        $this->jsUrl->addUrls($this->dataHelper->getControllerActions('TikTokShop_Log_Order'));

        $this->jsTranslator->addTranslations([
            'Please enter correct Product ID or SKU.' => __('Please enter correct Product ID or SKU.'),
            'Please enter correct Product ID.' => __('Please enter correct Product ID.'),
            'Edit Shipping Address' => __('Edit Shipping Address'),
        ]);

        $this->js->add(
            <<<JS
    require([
        'TikTokShop/Order/Edit/Item'
    ], function(){
        window.OrderEditItemObj = new OrderEditItem();
    });
JS
        );

        return parent::_prepareLayout();
    }
}
