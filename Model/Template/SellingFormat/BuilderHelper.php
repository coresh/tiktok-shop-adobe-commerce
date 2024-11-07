<?php

namespace M2E\TikTokShop\Model\Template\SellingFormat;

use M2E\TikTokShop\Model\Template\SellingFormat as SellingFormat;

class BuilderHelper
{
    /**
     * @param string $type
     * @param array $input
     *
     * @return array
     */
    public static function getPriceModifierData(string $type, array $input): array
    {
        $keyPriceModifierMode = $type . "_modifier_mode";
        $keyPriceModifierValue = $type . "_modifier_value";
        $keyPriceModifierAttribute = $type . "_modifier_attribute";

        $priceModifierData = [];
        if (
            !empty($input[$keyPriceModifierMode])
            && is_array($input[$keyPriceModifierMode])
        ) {
            foreach ($input[$keyPriceModifierMode] as $key => $priceModifierMode) {
                if (
                    !isset($input[$keyPriceModifierValue][$key])
                    || !is_string($input[$keyPriceModifierValue][$key])
                    || !isset($input[$keyPriceModifierAttribute][$key])
                    || !is_string($input[$keyPriceModifierAttribute][$key])
                ) {
                    continue;
                }

                if ($priceModifierMode == SellingFormat::PRICE_MODIFIER_ATTRIBUTE) {
                    $priceModifierData[] = [
                        'mode' => $priceModifierMode,
                        'attribute_code' => $input[$keyPriceModifierAttribute][$key],
                    ];
                } else {
                    $priceModifierData[] = [
                        'mode' => $priceModifierMode,
                        'value' => $input[$keyPriceModifierValue][$key],
                    ];
                }
            }
        }

        return $priceModifierData;
    }
}
