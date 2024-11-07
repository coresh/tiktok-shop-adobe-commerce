<?php

namespace M2E\TikTokShop\Helper\Data\Product;

class Identifier
{
    public const ISBN = 'ISBN';
    public const UPC = 'UPC';
    public const EAN = 'EAN';
    public const GTIN = 'GTIN';

    /**
     * @param string $string
     *
     * @return bool
     */
    public static function isISBN($string): bool
    {
        return self::isISBN10($string) || self::isISBN13($string);
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public static function isISBN10($string): bool
    {
        $string = (string)$string;
        if (strlen($string) !== 10) {
            return false;
        }

        $a = 0;
        for ($i = 0; $i < 10; $i++) {
            if ($string[$i] === "X" || $string[$i] === "x") {
                $a += 10 * (10 - $i);
            } elseif (is_numeric($string[$i])) {
                $a += (int)$string[$i] * (10 - $i);
            } else {
                return false;
            }
        }

        return $a % 11 === 0;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public static function isISBN13($string): bool
    {
        $string = (string)$string;
        if (strlen($string) !== 13) {
            return false;
        }

        if (strpos($string, '978') !== 0) {
            return false;
        }

        $check = 0;
        for ($i = 0; $i < 13; $i += 2) {
            $check += (int)$string[$i];
        }
        for ($i = 1; $i < 12; $i += 2) {
            $check += 3 * (int)$string[$i];
        }

        return $check % 10 === 0;
    }

    // ----------------------------------------

    /**
     * @param string $gtin
     *
     * @return bool
     */
    public static function isGTIN($gtin): bool
    {
        return self::isWorldWideId($gtin, self::GTIN);
    }

    /**
     * @param string $upc
     *
     * @return bool
     */
    public static function isUPC($upc): bool
    {
        return self::isWorldWideId($upc, self::UPC);
    }

    /**
     * @param string $ean
     *
     * @return bool
     */
    public static function isEAN($ean): bool
    {
        return self::isWorldWideId($ean, self::EAN);
    }

    // ---------------------------------------

    /**
     * @param string $worldWideId
     * @param string $type
     *
     * @return bool
     */
    private static function isWorldWideId($worldWideId, $type): bool
    {
        $adapters = [
            self::UPC => [
                12 => 'Upca',
            ],
            self::EAN => [
                13 => 'Ean13',
            ],
            self::GTIN => [
                12 => 'Gtin12',
                13 => 'Gtin13',
                14 => 'Gtin14',
            ],
        ];

        $length = strlen((string)$worldWideId);

        if (!isset($adapters[$type][$length])) {
            return false;
        }

        try {
            $validator = new \Laminas\Validator\Barcode($adapters[$type][$length]);
            $result = $validator->isValid($worldWideId);
        } catch (\Throwable $e) {
            return false;
        }

        return $result;
    }

    /**
     * @param string $identifier
     *
     * @return null|string
     */
    public static function getIdentifierType(string $identifier): ?string
    {
        if (self::isISBN($identifier)) {
            return self::ISBN;
        }

        if (self::isUPC($identifier)) {
            return self::UPC;
        }

        if (self::isEAN($identifier)) {
            return self::EAN;
        }

        if (self::isGTIN($identifier)) {
            return self::GTIN;
        }

        return null;
    }
}
