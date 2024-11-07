<?php

namespace M2E\TikTokShop\Model\Order\Item;

class ProxyObject extends \M2E\TikTokShop\Model\AbstractModel
{
    private \M2E\TikTokShop\Model\Order\Item $item;

    private $qty;

    private $price;

    private $subtotal;

    private array $additionalData = [];

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item $item
    ) {
        parent::__construct();
        $this->item = $item;
        $this->subtotal = $this->getOriginalPrice() * $this->getOriginalQty();
    }

    /**
     * @return float
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getOriginalPrice()
    {
        $price = $this->item->getSalePriceWithPlatformDiscount();

        if (($this->getProxyOrder()->isTaxModeNone() && $this->hasTax()) || $this->isVatTax()) {
            $price += $this->item->getTaxAmount();
        }

        return $price;
    }

    /**
     * @return int
     */
    public function getOriginalQty()
    {
        return $this->item->getQtyPurchased();
    }

    /**
     * @return \M2E\TikTokShop\Model\Order\ProxyObject
     */
    public function getProxyOrder()
    {
        return $this->item->getOrder()->getProxy();
    }

    /**
     * @param \M2E\TikTokShop\Model\Order\Item\ProxyObject $that
     *
     * @return bool
     */
    public function equals(\M2E\TikTokShop\Model\Order\Item\ProxyObject $that)
    {
        if ($this->getProductId() === null || $that->getProductId() === null) {
            return false;
        }

        if ($this->getProductId() != $that->getProductId()) {
            return false;
        }

        $thisOptions = $this->getOptions();
        $thatOptions = $that->getOptions();

        $thisOptionsKeys = array_keys($thisOptions);
        $thatOptionsKeys = array_keys($thatOptions);

        $thisOptionsValues = array_values($thisOptions);
        $thatOptionsValues = array_values($thatOptions);

        if (
            count($thisOptions) != count($thatOptions)
            || count(array_diff($thisOptionsKeys, $thatOptionsKeys)) > 0
            || count(array_diff($thisOptionsValues, $thatOptionsValues)) > 0
        ) {
            return false;
        }

        // grouped products have no options, that's why we have to compare associated products
        $thisAssociatedProducts = $this->getAssociatedProducts();
        $thatAssociatedProducts = $that->getAssociatedProducts();

        if (
            count($thisAssociatedProducts) != count($thatAssociatedProducts)
            || count(array_diff($thisAssociatedProducts, $thatAssociatedProducts)) > 0
        ) {
            return false;
        }

        return true;
    }

    public function merge(\M2E\TikTokShop\Model\Order\Item\ProxyObject $that)
    {
        $this->setQty($this->getQty() + $that->getOriginalQty());
        $this->subtotal += $that->getOriginalPrice() * $that->getOriginalQty();

        // merge additional data
        // ---------------------------------------
        $this->additionalData = [];
        // ---------------------------------------
    }

    public function pretendedToBeSimple(): bool
    {
        return $this->item->pretendedToBeSimple();
    }

    public function getProduct()
    {
        return $this->item->getProduct();
    }

    public function getProductId()
    {
        return $this->item->getMagentoProductId();
    }

    public function getMagentoProduct()
    {
        return $this->item->getMagentoProduct();
    }

    public function getOptions()
    {
        return $this->item->getAssociatedOptions();
    }

    public function getAssociatedProducts()
    {
        return $this->item->getAssociatedProducts();
    }

    public function getBasePrice()
    {
        return $this->getProxyOrder()->convertPriceToBase($this->getPrice());
    }

    /**
     * @param float $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        if ($price <= 0) {
            throw new \InvalidArgumentException('Price cannot be less than zero.');
        }

        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        if ($this->price !== null) {
            return $this->price;
        }

        return $this->subtotal / $this->getQty();
    }

    public function setQty($qty)
    {
        if ((int)$qty <= 0) {
            throw new \InvalidArgumentException('QTY cannot be less than zero.');
        }

        $this->qty = (int)$qty;

        return $this;
    }

    public function getQty()
    {
        if ($this->qty !== null) {
            return $this->qty;
        }

        return $this->getOriginalQty();
    }

    public function hasTax()
    {
        return $this->getProxyOrder()->hasTax();
    }

    public function isSalesTax()
    {
        return $this->getProxyOrder()->isSalesTax();
    }

    public function isVatTax()
    {
        return $this->getProxyOrder()->isVatTax();
    }

    /**
     * @return int|float
     */
    public function getTaxRate()
    {
        return $this->getProxyOrder()->getProductPriceTaxRate();
    }

    /**
     * @return \M2E\TikTokShop\Model\Order\Tax\PriceTaxRateInterface|null
     */
    public function getProductPriceTaxRateObject(): ?\M2E\TikTokShop\Model\Order\Tax\PriceTaxRateInterface
    {
        return $this->getProxyOrder()->getProductPriceTaxRateObject();
    }

    public function getGiftMessage()
    {
        return null;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
