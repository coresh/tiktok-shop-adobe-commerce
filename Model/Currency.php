<?php

namespace M2E\TikTokShop\Model;

use Magento\Store\Model\Store;

class Currency
{
    private \Magento\Store\Model\StoreManagerInterface $storeManager;
    private \Magento\Framework\Locale\CurrencyInterface $currency;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currency
    ) {
        $this->storeManager = $storeManager;
        $this->currency = $currency;
    }

    public function isBase($currencyCode, $store)
    {
        $baseCurrency = $this->storeManager->getStore($store)->getBaseCurrencyCode();

        return $baseCurrency == $currencyCode;
    }

    public function isAllowed($currencyCode, $store)
    {
        $allowedCurrencies = $this->storeManager->getStore($store)->getAvailableCurrencyCodes();

        return in_array($currencyCode, $allowedCurrencies);
    }

    public function getConvertRateFromBase($currencyCode, $store, $precision = 2)
    {
        if (!$this->isAllowed($currencyCode, $store)) {
            return 0;
        }

        $precision = (int)$precision;

        if ($precision <= 0) {
            $precision = 2;
        }

        $rate = (float)$this->storeManager->getStore($store)->getBaseCurrency()->getRate($currencyCode);

        return round($rate, $precision);
    }

    public function isConvertible($currencyCode, $store)
    {
        if (
            $this->isBase($currencyCode, $store)
            || !$this->isAllowed($currencyCode, $store)
            || $this->getConvertRateFromBase($currencyCode, $store) == 0
        ) {
            return false;
        }

        return true;
    }

    public function convertPrice($price, $currencyCode, $store)
    {
        if (!$this->isConvertible($currencyCode, $store)) {
            return $price;
        }

        return $this->storeManager->getStore($store)->getBaseCurrency()->convert($price, $currencyCode);
    }

    public function convertPriceToBaseCurrency($price, $currencyCode, $store)
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore($store);

        if (in_array($currencyCode, $store->getAvailableCurrencyCodes(true))) {
            $currencyConvertRate = $store->getBaseCurrency()->getRate($currencyCode);
            $currencyConvertRate == 0 && $currencyConvertRate = 1;
            $price = $price / $currencyConvertRate;
        }

        return $price;
    }

    public function formatPrice($currencyName, $priceValue)
    {
        return $this->currency->getCurrency($currencyName)->toCurrency($priceValue);
    }
}
