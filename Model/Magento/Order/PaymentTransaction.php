<?php

namespace M2E\TikTokShop\Model\Magento\Order;

use M2E\TikTokShop\Model\AbstractModel;

/**
 * Class \M2E\TikTokShop\Model\Magento\Order\PaymentTransaction
 */
class PaymentTransaction extends AbstractModel
{
    /** @var \Magento\Sales\Model\Order $magentoOrder */
    protected $magentoOrder = null;

    /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
    protected $transaction = null;

    //########################################

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     *
     * @return $this
     */
    public function setMagentoOrder(\Magento\Sales\Model\Order $magentoOrder)
    {
        $this->magentoOrder = $magentoOrder;

        return $this;
    }

    //########################################

    public function getPaymentTransaction()
    {
        return $this->transaction;
    }

    //########################################

    public function buildPaymentTransaction()
    {
        $payment = $this->magentoOrder->getPayment();

        if (empty($payment)) {
            return;
        }

        $transactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
        if ($this->getData('sum') < 0) {
            $transactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_REFUND;
        }

        $existTransaction = $payment->getTransaction($this->getData('transaction_id'));

        if ($existTransaction && $existTransaction->getTxnType() == $transactionType) {
            return null;
        }

        $payment->setTransactionId($this->getData('transaction_id'));
        $this->transaction = $payment->addTransaction($transactionType);

        if (defined('\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS')) {
            $this->unsetData('transaction_id');
            $this->transaction->setAdditionalInformation(
                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                $this->getData()
            );
        }

        $this->transaction->save();
    }

    //########################################
}
