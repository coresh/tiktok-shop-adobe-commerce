<?php

namespace M2E\TikTokShop\Plugin\StockItem\Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;

use M2E\TikTokShop\Model\Magento\Quote\Builder;

class QuoteItemQtyList extends \M2E\TikTokShop\Plugin\AbstractPlugin
{
    public function aroundGetQty($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getQty', $interceptor, $callback, $arguments);
    }

    /**
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList $interceptor
     * @param \Closure $callback
     * @param array $arguments
     *
     * @return mixed
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function processGetQty($interceptor, \Closure $callback, array $arguments)
    {
        $quoteItemId = $arguments[1];
        $quoteId = $arguments[2];
        $itemQty = &$arguments[3];

        /** @var \M2E\TikTokShop\Helper\Data\GlobalData $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\TikTokShop\Helper\Data\GlobalData::class
        );

        if ($helper->getValue(Builder::PROCESS_QUOTE_ID) == $quoteId) {
            empty($quoteItemId) && $itemQty = 0;
        }

        return $callback(...$arguments);
    }

    //########################################
}
