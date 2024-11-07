<?php

namespace M2E\TikTokShop\Observer\Order\Service\Quote\Submit;

class Before extends \M2E\TikTokShop\Observer\AbstractObserver
{
    protected function process(): void
    {
        /** @var \Magento\Sales\Model\Order $magentoOrder */
        /** @var \Magento\Quote\Model\Quote $quote */

        $magentoOrder = $this->getEvent()->getOrder();
        $quote = $this->getEvent()->getQuote();

        if ($quote->getIsTikTokShopQuote()) {
            $magentoOrder->setCanSendNewEmailFlag($quote->getIsNeedToSendEmail());
        }
    }
}
