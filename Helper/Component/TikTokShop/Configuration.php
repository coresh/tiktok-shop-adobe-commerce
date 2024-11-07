<?php

namespace M2E\TikTokShop\Helper\Component\TikTokShop;

class Configuration
{
    public const IDENTIFIER_CODE_MODE_NOT_SET          = 0;
    public const IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE = 1;

    public const PACKAGE_MODE_NOT_SET          = 0;
    public const PACKAGE_MODE_CUSTOM_ATTRIBUTE = 1;
    public const PACKAGE_MODE_CUSTOM_VALUE     = 2;

    public const DIMENSION_TYPE_WIDTH  = 'width';
    public const DIMENSION_TYPE_LENGTH = 'length';
    public const DIMENSION_TYPE_HEIGHT = 'height';
    public const DIMENSION_TYPE_WEIGHT = 'weight';

    public const CONFIG_GROUP = '/tts/configuration/';

    private \M2E\TikTokShop\Model\Config\Manager $config;

    public function __construct(\M2E\TikTokShop\Model\Config\Manager $config)
    {
        $this->config = $config;
    }

    //region Identifier
    public function getIdentifierCodeMode(): int
    {
        return (int)$this->config->getGroupValue(self::CONFIG_GROUP, 'identifier_code_mode');
    }

    public function getIdentifierCodeCustomAttribute()
    {
        return $this->config->getGroupValue(self::CONFIG_GROUP, 'identifier_code_custom_attribute');
    }

    public function isIdentifierCodeModeCustomAttribute(): bool
    {
        return $this->getIdentifierCodeMode() == self::IDENTIFIER_CODE_MODE_CUSTOM_ATTRIBUTE;
    }

    //endregion

    public function getPackageDimensionMode(string $dimensionType): int
    {
        return (int)$this->config->getGroupValue(
            self::CONFIG_GROUP,
            $this->makeModeKey($dimensionType)
        );
    }

    public function getPackageDimensionCustomAttribute(string $dimensionType): string
    {
        return (string)$this->config->getGroupValue(
            self::CONFIG_GROUP,
            $this->makeCustomAttributeKey($dimensionType)
        );
    }

    public function getPackageDimensionCustomValue(string $dimensionType): string
    {
        return (string)$this->config->getGroupValue(
            self::CONFIG_GROUP,
            $this->makeCustomValueKey($dimensionType)
        );
    }

    public function setConfigValues(array $values): void
    {
        if (isset($values['identifier_code_mode'])) {
            $this->config->setGroupValue(
                self::CONFIG_GROUP,
                'identifier_code_mode',
                $values['identifier_code_mode']
            );
        }

        if (isset($values['identifier_code_custom_attribute'])) {
            $this->config->setGroupValue(
                self::CONFIG_GROUP,
                'identifier_code_custom_attribute',
                $values['identifier_code_custom_attribute']
            );
        }

        $dimensionTypes = [
            self::DIMENSION_TYPE_WIDTH,
            self::DIMENSION_TYPE_HEIGHT,
            self::DIMENSION_TYPE_LENGTH,
            self::DIMENSION_TYPE_WEIGHT,
        ];

        foreach ($dimensionTypes as $packageDimension) {
            //region Set Mode
            $modeKey = $this->makeModeKey($packageDimension);
            $mode = $values[$modeKey] ?? 0;
            $this->config->setGroupValue(self::CONFIG_GROUP, $modeKey, $mode);
            //endregion

            //region Set Custom Attribute
            $customAttributeKey = $this->makeCustomAttributeKey($packageDimension);
            $customAttribute = $values[$customAttributeKey] ?? '';
            if ($mode != self::PACKAGE_MODE_CUSTOM_ATTRIBUTE) {
                $customAttribute = '';
            }
            $this->config->setGroupValue(self::CONFIG_GROUP, $customAttributeKey, $customAttribute);
            //endregion

            //region Set Custom Value
            $customValueKey = $this->makeCustomValueKey($packageDimension);
            $customValue = $values[$customValueKey] ?? '';
            if ($mode != self::PACKAGE_MODE_CUSTOM_VALUE) {
                $customValue = '';
            }
            $this->config->setGroupValue(self::CONFIG_GROUP, $customValueKey, $customValue);
            //endregion
        }
    }

    /**
     * Generated:
     * - package_width_mode
     * - package_length_mode
     * - package_height_mode
     * - package_weight_mode
     */
    private function makeModeKey(string $dimensionType): string
    {
        return "package_{$dimensionType}_mode";
    }

    /**
     * Generated:
     * - package_width_custom_attribute
     * - package_length_custom_attribute
     * - package_height_custom_attribute
     * - package_weight_custom_attribute
     */
    private function makeCustomAttributeKey(string $dimensionType): string
    {
        return "package_{$dimensionType}_custom_attribute";
    }

    /**
     * Generated:
     * - package_width_custom_value
     * - package_length_custom_value
     * - package_height_custom_value
     * - package_weight_custom_value
     */
    private function makeCustomValueKey(string $dimensionType): string
    {
        return "package_{$dimensionType}_custom_value";
    }
}
