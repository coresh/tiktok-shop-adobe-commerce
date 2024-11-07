<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Module;

class Debugger
{
    /** @link https://php.net/manual/en/datetime.format.php */
    private const DATE_POINT_FORMAT = 'H:i:s';

    /** @link https://php.net/manual/en/dateinterval.format.php */
    private const DATE_POINT_INTERVAL_FORMAT = '%s.%f';
    private const DATE_POINT_INTERVAL_FORMAT_UNIT_OF_MEASURE = 's';

    private static \M2E\TikTokShop\Helper\Module\Logger $logger;
    private static bool $isEnables = false;
    private static array $collectData = [];
    /** @var \DateTime[] */
    private static array $datePointCollect = [];
    private static bool $isEnablesDatePoint = false;

    public static function enable(): void
    {
        self::$isEnables = true;
    }

    /**
     * @see self::collectData()
     */
    public static function enableWithDatePoints(): void
    {
        self::$isEnables = true;
        self::$isEnablesDatePoint = true;
    }

    public static function disable(): void
    {
        self::$isEnables = false;
        self::$isEnablesDatePoint = false;
    }

    public static function write(array $data, string $label = 'debug'): void
    {
        if (!self::$isEnables) {
            return;
        }

        self::getLogger()->process(
            $data,
            $label
        );
    }

    // ----------------------------------------

    public static function collectData(array $data, string $label = 'general'): void
    {
        if (!isset(self::$collectData[$label])) {
            self::$collectData[$label] = [];
        }

        if (self::$isEnablesDatePoint) {
            self::collectDatePoint($label);
        }

        self::$collectData[$label] = $data;
    }

    public static function flushCollectedData(string $label = 'debug'): void
    {
        if (empty(self::$collectData)) {
            return;
        }

        $collectData = self::$isEnablesDatePoint
            ? self::mergeCollectDataWithDatePoints(self::$collectData)
            : self::$collectData;

        self::write($collectData, $label);
    }

    // ----------------------------------------

    private static function collectDatePoint(string $label): void
    {
        if (!isset(self::$datePointCollect[$label])) {
            self::$datePointCollect[$label] = [];
        }

        self::$datePointCollect[$label] = \M2E\TikTokShop\Helper\Date::createCurrentGmt();
    }

    private static function mergeCollectDataWithDatePoints(array $collectData): array
    {
        /** @var \DateTime|null $previousDatePoint */
        $previousDatePoint = null;
        foreach ($collectData as $label => &$data) {
            if (!isset(self::$datePointCollect[$label])) {
                continue;
            }
            $datePoint = self::$datePointCollect[$label];

            if (!isset($data['date_interval'])) {
                $dateInterval = $previousDatePoint
                    ? $datePoint->diff($previousDatePoint)->format(self::DATE_POINT_INTERVAL_FORMAT)
                    : 0;
                $data['date_interval'] = $dateInterval . self::DATE_POINT_INTERVAL_FORMAT_UNIT_OF_MEASURE;
            }

            $previousDatePoint = $datePoint;

            if (!isset($data['recorded_at'])) {
                $data['recorded_at'] = $datePoint->format(self::DATE_POINT_FORMAT);
            }
        }

        return $collectData;
    }

    // ----------------------------------------

    private static function getLogger(): \M2E\TikTokShop\Helper\Module\Logger
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset(self::$logger)) {
            self::$logger = \Magento\Framework\App\ObjectManager::getInstance()
                                                                ->get(\M2E\TikTokShop\Helper\Module\Logger::class);
        }

        return self::$logger;
    }
}
