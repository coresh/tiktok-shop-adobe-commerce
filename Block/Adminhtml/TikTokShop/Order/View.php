<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Order;

use M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractContainer;

class View extends AbstractContainer
{
    /** @var \M2E\TikTokShop\Helper\Data\GlobalData */
    private $globalDataHelper;
    private \M2E\TikTokShop\Helper\Url $urlHelper;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\TikTokShop\Helper\Url $urlHelper,
        \M2E\TikTokShop\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ttsOrderView');
        $this->_controller = 'adminhtml_tikTokShop_order';
        $this->_mode = 'view';

        /** @var \M2E\TikTokShop\Model\Order $order */
        $order = $this->globalDataHelper->getValue('order');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->urlHelper->getBackUrl('*/tiktokshop_order/index');
        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => 'CommonObj.backClick(\'' . $url . '\')',
            'class' => 'back',
        ]);

        if ($order->canUpdateShippingStatus()) {
            $url = $this->getUrl('*/*/updateShippingStatus', ['id' => $order->getId()]);
            $this->addButton('ship', [
                'label' => __('Mark as Shipped'),
                'onclick' => "setLocation('" . $url . "');",
                'class' => 'primary',
            ]);
        }

        if ($order->getReserve()->isPlaced()) {
            $url = $this->getUrl('*/order/reservationCancel', ['ids' => $order->getId()]);
            $this->addButton('reservation_cancel', [
                'label' => __('Cancel QTY Reserve'),
                'onclick' => "confirmSetLocation(TikTokShop.translator.translate('Are you sure?'), '" . $url . "');",
                'class' => 'primary',
            ]);
        } elseif ($order->isReservable()) {
            $url = $this->getUrl('*/order/reservationPlace', ['ids' => $order->getId()]);
            $this->addButton('reservation_place', [
                'label' => __('Reserve QTY'),
                'onclick' => "confirmSetLocation(TikTokShop.translator.translate('Are you sure?'), '" . $url . "');",
                'class' => 'primary',
            ]);
        }

        if ($order->canCreateMagentoOrder()) {
            $url = $this->getUrl('*/*/createMagentoOrder', ['id' => $order->getId()]);
            $this->addButton('order', [
                'label' => __('Create Magento Order'),
                'onclick' => "setLocation('" . $url . "');",
                'class' => 'primary',
            ]);
        }
    }

    protected function _beforeToHtml()
    {
        $this->js->addRequireJs(['debug' => 'TikTokShop/Order/Debug'], '');

        return parent::_beforeToHtml();
    }
}
