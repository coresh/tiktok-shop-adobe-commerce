<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Magento;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    public const ADDITIONAL_DATA_KEY_PAYMENT_METHOD = 'payment_method';
    public const ADDITIONAL_DATA_KEY_CHANNEL_ORDER_ID = 'channel_order_id';

    protected $_code = 'tiktokshoppayment';

    protected $_canUseCheckout = false;
    protected $_canUseInternal = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    protected $_infoBlockType = \M2E\TikTokShop\Block\Adminhtml\Magento\Payment\Info::class;

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return true;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $data = $data->getData()['additional_data'];

        $details = [
            self::ADDITIONAL_DATA_KEY_PAYMENT_METHOD => $data[self::ADDITIONAL_DATA_KEY_PAYMENT_METHOD],
            self::ADDITIONAL_DATA_KEY_CHANNEL_ORDER_ID => $data[self::ADDITIONAL_DATA_KEY_CHANNEL_ORDER_ID],
        ];

        $this->getInfoInstance()->setAdditionalInformation($details);

        return $this;
    }
}
