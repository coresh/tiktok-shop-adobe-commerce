<?php

namespace M2E\TikTokShop\Model\Magento;

/**
 * Class \M2E\TikTokShop\Model\Magento\Payment
 */
class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'tiktokshoppayment';

    protected $_canUseCheckout = false;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    protected $_infoBlockType = \M2E\TikTokShop\Block\Adminhtml\Magento\Payment\Info::class;

    //########################################

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $data = $data->getData()['additional_data'];

        $details = [
            'payment_method' => $data['payment_method'],
            'channel_order_id' => $data['channel_order_id'],
            'cash_on_delivery_cost' => $data['cash_on_delivery_cost'] ?? null,
            'transactions' => $data['transactions'],
            'tax_id' => isset($data['tax_id']) ? $data['tax_id'] : null,
        ];

        $this->getInfoInstance()->setAdditionalInformation($details);

        return $this;
    }

    //########################################
}
