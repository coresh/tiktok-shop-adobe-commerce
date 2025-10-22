<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Shop;

class Region
{
    public const REGION_US = 'US';
    public const REGION_GB = 'GB';
    public const REGION_ES = 'ES';
    public const REGION_IE = 'IE';
    public const REGION_MX = 'MX';
    public const REGION_DE = 'DE';
    public const REGION_FR = 'FR';
    public const REGION_IT = 'IT';
    public const REGION_BR = 'BR';

    public const CURRENCY_EUR = 'EUR';
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_GBP = 'GBP';
    public const CURRENCY_MXN = 'MXN';
    public const CURRENCY_BRL = 'BRL';

    public const SIZE_DIMENSION_CENTIMETER = 'CENTIMETER';
    public const SIZE_DIMENSION_INCH = 'INCH';

    public const WEIGHT_DIMENSION_KILOGRAM = 'KILOGRAM';
    public const WEIGHT_DIMENSION_POUND = 'POUND';

    public const EU_REGION_CODES = [
        self::REGION_ES,
        self::REGION_IE,
        self::REGION_DE,
        self::REGION_FR,
        self::REGION_IT,
    ];

    private const US_REGION_CODES = [
        self::REGION_US,
        self::REGION_MX,
    ];

    private string $regionCode;
    private string $label;
    private string $currency;
    private string $sizeDimension;
    private string $weightDimension;
    private \M2E\TikTokShop\Model\Shop\Region\PackageWeightRestrictions $packageWeightRestrictions;
    private \M2E\TikTokShop\Model\Shop\Region\ProductPriceRestrictions $productPriceRestrictions;

    public function __construct(
        string $regionCode,
        string $label,
        string $currency,
        string $sizeDimension,
        string $weightDimension,
        \M2E\TikTokShop\Model\Shop\Region\PackageWeightRestrictions $packageWeightRestrictions,
        \M2E\TikTokShop\Model\Shop\Region\ProductPriceRestrictions $productPriceRestrictions
    ) {
        $this->regionCode = $regionCode;
        $this->label = $label;
        $this->currency = $currency;
        $this->sizeDimension = $sizeDimension;
        $this->weightDimension = $weightDimension;
        $this->packageWeightRestrictions = $packageWeightRestrictions;
        $this->productPriceRestrictions = $productPriceRestrictions;
    }

    public function getRegionCode(): string
    {
        return $this->regionCode;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getSizeDimension(): string
    {
        return $this->sizeDimension;
    }

    public function getWeightDimension(): string
    {
        return $this->weightDimension;
    }

    public function getPackageWeightRestrictions(): Region\PackageWeightRestrictions
    {
        return $this->packageWeightRestrictions;
    }

    public function getProductPriceRestrictions(): Region\ProductPriceRestrictions
    {
        return $this->productPriceRestrictions;
    }

    // ----------------------------------------

    public function isEU(): bool
    {
        return in_array($this->regionCode, self::EU_REGION_CODES);
    }

    public function isUS(): bool
    {
        return in_array($this->regionCode, self::US_REGION_CODES);
    }

    public function isRegionCodeUS(): bool
    {
        return $this->regionCode === self::REGION_US;
    }

    public function isRegionCodeGB(): bool
    {
        return $this->regionCode === self::REGION_GB;
    }

    public function isRegionCodeIT(): bool
    {
        return $this->regionCode === self::REGION_IT;
    }

    public function isRegionCodeDE(): bool
    {
        return $this->regionCode === self::REGION_DE;
    }

    public function isRegionCodeES(): bool
    {
        return $this->regionCode === self::REGION_ES;
    }

    public function isRegionCodeFR(): bool
    {
        return $this->regionCode === self::REGION_FR;
    }

    public function isRegionCodeIE(): bool
    {
        return $this->regionCode === self::REGION_IE;
    }
    public function isRegionCodeBR(): bool
    {
        return $this->regionCode === self::REGION_BR;
    }

    public function getLocale(): string
    {
        $locales = [
            \M2E\TikTokShop\Model\Shop\Region::REGION_DE => 'de-DE',
            \M2E\TikTokShop\Model\Shop\Region::REGION_IE => 'en-IE',
            \M2E\TikTokShop\Model\Shop\Region::REGION_ES => 'es-ES',
            \M2E\TikTokShop\Model\Shop\Region::REGION_FR => 'fr-FR',
            \M2E\TikTokShop\Model\Shop\Region::REGION_IT => 'it-IT',
        ];

        return $locales[$this->getRegionCode()] ?? 'en-US';
    }
}
