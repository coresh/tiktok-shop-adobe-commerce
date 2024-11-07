<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Payment;

class Info extends \Magento\Payment\Block\Info
{
    private \Magento\Sales\Model\OrderFactory $orderFactory;

    private ?\Magento\Sales\Model\Order $order = null;

    protected $_template = 'M2E_TikTokShop::magento/order/payment/info.phtml';

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderFactory = $orderFactory;
    }

    //########################################

    /**
     * Magento has forcibly set FRONTEND area
     * vendor/magento/module-payment/Helper/Data.php::getInfoBlockHtm()
     * @return string
     */
    protected function _toHtml()
    {
        $this->setData('area', \Magento\Framework\App\Area::AREA_ADMINHTML);

        return parent::_toHtml();
    }

    //########################################

    /**
     * @return \Magento\Sales\Model\Order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrder(): ?\Magento\Sales\Model\Order
    {
        if ($this->order !== null) {
            return $this->order;
        }

        $orderId = $this->getInfo()->getData('parent_id');
        if (empty($orderId)) {
            return null;
        }

        $this->order = $this->orderFactory->create();
        $this->order->load($orderId);

        return $this->order;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPaymentMethod(): string
    {
        return (string)$this->getInfo()->getAdditionalInformation('payment_method');
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChannelOrderId(): string
    {
        return (string)$this->getInfo()->getAdditionalInformation('channel_order_id');
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTaxId(): string
    {
        return (string)$this->getInfo()->getAdditionalInformation('tax_id');
    }

    public function getChannelOrderUrl(): string
    {
        return '';
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCashOnDeliveryCost(): float
    {
        if ($this->getIsSecureMode()) {
            return 0.0;
        }

        return (float)$this->getInfo()->getAdditionalInformation('cash_on_delivery_cost');
    }
}
