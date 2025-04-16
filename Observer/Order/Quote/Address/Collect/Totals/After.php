<?php

namespace M2E\TikTokShop\Observer\Order\Quote\Address\Collect\Totals;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class After extends \M2E\TikTokShop\Observer\AbstractObserver
{
    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    public function __construct(
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return void
     */
    public function process(): void
    {
        /** @var \Magento\Quote\Model\Quote\Address\Total $total */
        /** @var \Magento\Quote\Model\Quote $quote */
        $total = $this->getEvent()->getTotal();
        $quote = $this->getEvent()->getQuote();

        if ($quote->getIsTikTokShopQuote() && $quote->getUseTikTokShopDiscount()) {
            $discountAmount = $this->priceCurrency->convert($quote->getCoinDiscount());

            if ($total->getTotalAmount('subtotal')) {
                $total->setTotalAmount('subtotal', $total->getTotalAmount('subtotal') - $discountAmount);
            }

            if ($total->getBaseTotalAmount('subtotal')) {
                $total->setTotalAmount('subtotal', $total->getBaseTotalAmount('subtotal') - $discountAmount);
            }

            if ($total->hasData('grand_total') && $total->getGrandTotal()) {
                $total->setGrandTotal($total->getGrandTotal() - $discountAmount);
            }

            if ($total->hasData('base_grand_total') && $total->getBaseGrandTotal()) {
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $discountAmount);
            }
        }
    }
}
