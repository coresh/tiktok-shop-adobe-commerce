<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\SellingFormat;

use M2E\TikTokShop\Model\Template\SellingFormat as SellingFormat;
use M2E\TikTokShop\Model\ResourceModel\Template\SellingFormat as ResourceSellingFormat;

class Builder extends \M2E\TikTokShop\Model\TikTokShop\Template\AbstractBuilder
{
    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    protected function prepareData(): array
    {
        $data = parent::prepareData();

        $data = array_merge($this->getDefaultData(), $data);

        if (isset($this->rawData['listing_type'])) {
            $data['listing_type'] = (int)$this->rawData['listing_type'];
        }

        if (isset($this->rawData['listing_is_private'])) {
            $data['listing_is_private'] = (int)(bool)$this->rawData['listing_is_private'];
        }

        if (isset($this->rawData['listing_type_attribute'])) {
            $data['listing_type_attribute'] = $this->rawData['listing_type_attribute'];
        }

        if (isset($this->rawData['duration_mode'])) {
            $data['duration_mode'] = (int)$this->rawData['duration_mode'];
        }

        if (isset($this->rawData['duration_attribute'])) {
            $data['duration_attribute'] = $this->rawData['duration_attribute'];
        }

        if (isset($this->rawData['qty_mode'])) {
            $data['qty_mode'] = (int)$this->rawData['qty_mode'];
        }

        if (isset($this->rawData['qty_custom_value'])) {
            $data['qty_custom_value'] = (int)$this->rawData['qty_custom_value'];
        }

        if (isset($this->rawData['qty_custom_attribute'])) {
            $data['qty_custom_attribute'] = $this->rawData['qty_custom_attribute'];
        }

        if (isset($this->rawData['qty_percentage'])) {
            $data['qty_percentage'] = (int)$this->rawData['qty_percentage'];
        }

        if (isset($this->rawData['qty_modification_mode'])) {
            $data['qty_modification_mode'] = (int)$this->rawData['qty_modification_mode'];
        }

        if (isset($this->rawData['qty_min_posted_value'])) {
            $data['qty_min_posted_value'] = (int)$this->rawData['qty_min_posted_value'];
        }

        if (isset($this->rawData['qty_max_posted_value'])) {
            $data['qty_max_posted_value'] = (int)$this->rawData['qty_max_posted_value'];
        }

        if (isset($this->rawData['lot_size_mode'])) {
            $data['lot_size_mode'] = (int)$this->rawData['lot_size_mode'];
        }

        if (isset($this->rawData['lot_size_custom_value'])) {
            $data['lot_size_custom_value'] = (int)$this->rawData['lot_size_custom_value'];
        }

        if (isset($this->rawData['lot_size_attribute'])) {
            $data['lot_size_attribute'] = $this->rawData['lot_size_attribute'];
        }

        if (isset($this->rawData['vat_mode'])) {
            $data['vat_mode'] = (int)$this->rawData['vat_mode'];
        }

        if (isset($this->rawData['vat_percent'])) {
            $data['vat_percent'] = (float)$this->rawData['vat_percent'];
        }

        if (isset($this->rawData['tax_table_mode'])) {
            $data['tax_table_mode'] = (int)$this->rawData['tax_table_mode'];
        }

        if (isset($this->rawData['tax_category_mode'])) {
            $data['tax_category_mode'] = (int)$this->rawData['tax_category_mode'];
        }

        if (isset($this->rawData['tax_category_value'])) {
            $data['tax_category_value'] = $this->rawData['tax_category_value'];
        }

        if (isset($this->rawData['tax_category_attribute'])) {
            $data['tax_category_attribute'] = $this->rawData['tax_category_attribute'];
        }

        if (isset($this->rawData['price_variation_mode'])) {
            $data['price_variation_mode'] = (int)$this->rawData['price_variation_mode'];
        }

        // ---------------------------------------

        if (isset($this->rawData['fixed_price_mode'])) {
            $data['fixed_price_mode'] = (int)$this->rawData['fixed_price_mode'];
        }

        $fixedPriceModifierData = $this->getFixedPriceModifierData();
        if ($fixedPriceModifierData !== null) {
            $data['fixed_price_modifier'] = \M2E\Core\Helper\Json::encode($fixedPriceModifierData);
        }

        if (isset($this->rawData['fixed_price_custom_attribute'])) {
            $data['fixed_price_custom_attribute'] = $this->rawData['fixed_price_custom_attribute'];
        }

        // ---------------------------------------

        if (isset($this->rawData['start_price_mode'])) {
            $data['start_price_mode'] = (int)$this->rawData['start_price_mode'];
        }

        if (isset($this->rawData['start_price_coefficient'], $this->rawData['start_price_coefficient_mode'])) {
            $data['start_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $this->rawData['start_price_coefficient'],
                $this->rawData['start_price_coefficient_mode']
            );
        }

        if (isset($this->rawData['start_price_custom_attribute'])) {
            $data['start_price_custom_attribute'] = $this->rawData['start_price_custom_attribute'];
        }

        // ---------------------------------------

        if (isset($this->rawData['reserve_price_mode'])) {
            $data['reserve_price_mode'] = (int)$this->rawData['reserve_price_mode'];
        }

        if (isset($this->rawData['reserve_price_coefficient'], $this->rawData['reserve_price_coefficient_mode'])) {
            $data['reserve_price_coefficient'] = $this->getFormattedPriceCoefficient(
                $this->rawData['reserve_price_coefficient'],
                $this->rawData['reserve_price_coefficient_mode']
            );
        }

        if (isset($this->rawData['reserve_price_custom_attribute'])) {
            $data['reserve_price_custom_attribute'] = $this->rawData['reserve_price_custom_attribute'];
        }

        // ---------------------------------------

        if (isset($this->rawData['price_discount_stp_mode'])) {
            $data['price_discount_stp_mode'] = (int)$this->rawData['price_discount_stp_mode'];
        }

        if (isset($this->rawData['price_discount_stp_attribute'])) {
            $data['price_discount_stp_attribute'] = $this->rawData['price_discount_stp_attribute'];
        }

        if (isset($this->rawData['price_discount_stp_type'])) {
            $data['price_discount_stp_type'] = (int)$this->rawData['price_discount_stp_type'];
        }

        // ---------------------------------------

        if (isset($this->rawData['price_discount_map_mode'])) {
            $data['price_discount_map_mode'] = (int)$this->rawData['price_discount_map_mode'];
        }

        if (isset($this->rawData['price_discount_map_attribute'])) {
            $data['price_discount_map_attribute'] = $this->rawData['price_discount_map_attribute'];
        }

        if (isset($this->rawData['price_discount_map_exposure_type'])) {
            $data['price_discount_map_exposure_type'] = (int)$this->rawData['price_discount_map_exposure_type'];
        }

        if (isset($this->rawData['restricted_to_business'])) {
            $data['restricted_to_business'] = (int)$this->rawData['restricted_to_business'];
        }

        // ---------------------------------------

        if (isset($this->rawData['best_offer_mode'])) {
            $data['best_offer_mode'] = (int)$this->rawData['best_offer_mode'];
        }

        if (isset($this->rawData['best_offer_accept_mode'])) {
            $data['best_offer_accept_mode'] = (int)$this->rawData['best_offer_accept_mode'];
        }

        if (isset($this->rawData['best_offer_accept_value'])) {
            $data['best_offer_accept_value'] = $this->rawData['best_offer_accept_value'];
        }

        if (isset($this->rawData['best_offer_accept_attribute'])) {
            $data['best_offer_accept_attribute'] = $this->rawData['best_offer_accept_attribute'];
        }

        if (isset($this->rawData['best_offer_reject_mode'])) {
            $data['best_offer_reject_mode'] = (int)$this->rawData['best_offer_reject_mode'];
        }

        if (isset($this->rawData['best_offer_reject_value'])) {
            $data['best_offer_reject_value'] = $this->rawData['best_offer_reject_value'];
        }

        if (isset($this->rawData['best_offer_reject_attribute'])) {
            $data['best_offer_reject_attribute'] = $this->rawData['best_offer_reject_attribute'];
        }

        if (isset($this->rawData['paypal_immediate_payment'])) {
            $data['paypal_immediate_payment'] = $this->rawData['paypal_immediate_payment'];
        }

        if (isset($this->rawData['ignore_variations'])) {
            $data['ignore_variations'] = (int)$this->rawData['ignore_variations'];
        }

        if (isset($this->rawData['cash_on_delivery'])) {
            $data['cash_on_delivery'] = $this->rawData['cash_on_delivery'];
        }

        if (isset($this->rawData[ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE])) {
            $data[ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE]
                = $this->rawData[ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE];
        }

        return $data;
    }

    /**
     * @param $priceCoefficient
     * @param $priceCoefficientMode
     *
     * @return string
     */
    private function getFormattedPriceCoefficient($priceCoefficient, $priceCoefficientMode): string
    {
        if ($priceCoefficientMode == SellingFormat::PRICE_COEFFICIENT_NONE) {
            return '';
        }

        $isCoefficientModeDecrease =
            $priceCoefficientMode == SellingFormat::PRICE_COEFFICIENT_ABSOLUTE_DECREASE ||
            $priceCoefficientMode == SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE;

        $isCoefficientModePercentage =
            $priceCoefficientMode == SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_DECREASE ||
            $priceCoefficientMode == SellingFormat::PRICE_COEFFICIENT_PERCENTAGE_INCREASE;

        $sign = $isCoefficientModeDecrease ? '-' : '+';
        $measuringSystem = $isCoefficientModePercentage ? '%' : '';

        return $sign . $priceCoefficient . $measuringSystem;
    }

    /**
     * @return array
     */
    public function getDefaultData(): array
    {
        return [

            'qty_mode' => \M2E\TikTokShop\Model\Template\SellingFormat::QTY_MODE_PRODUCT,
            'qty_custom_value' => 1,
            'qty_custom_attribute' => '',
            'qty_percentage' => 100,
            'qty_modification_mode' => SellingFormat::QTY_MODIFICATION_MODE_OFF,
            'qty_min_posted_value' => SellingFormat::QTY_MIN_POSTED_DEFAULT_VALUE,
            'qty_max_posted_value' => SellingFormat::QTY_MAX_POSTED_DEFAULT_VALUE,

            'fixed_price_mode' => \M2E\TikTokShop\Model\Template\SellingFormat::PRICE_MODE_PRODUCT,
            'fixed_price_modifier' => '[]',
            'fixed_price_custom_attribute' => '',

            'cash_on_delivery' => SellingFormat::CASH_ON_DELIVERY_OFF,

            ResourceSellingFormat::COLUMN_IS_NOT_FOR_SALE => SellingFormat::IS_NOT_FOR_SALE_OFF,
        ];
    }

    /**
     * @return array|null
     */
    private function getFixedPriceModifierData(): ?array
    {
        if (
            !empty($this->rawData['fixed_price_modifier_mode'])
            && is_array($this->rawData['fixed_price_modifier_mode'])
        ) {
            $fixedPriceModifierData = [];
            foreach ($this->rawData['fixed_price_modifier_mode'] as $key => $fixedPriceModifierMode) {
                if (
                    !isset($this->rawData['fixed_price_modifier_value'][$key])
                    || !is_string($this->rawData['fixed_price_modifier_value'][$key])
                    || !isset($this->rawData['fixed_price_modifier_attribute'][$key])
                    || !is_string($this->rawData['fixed_price_modifier_attribute'][$key])
                ) {
                    continue;
                }

                if ($fixedPriceModifierMode == SellingFormat::PRICE_COEFFICIENT_ATTRIBUTE) {
                    $fixedPriceModifierData[] = [
                        'mode' => $fixedPriceModifierMode,
                        'attribute_code' => $this->rawData['fixed_price_modifier_attribute'][$key],
                    ];
                } else {
                    $fixedPriceModifierData[] = [
                        'mode' => $fixedPriceModifierMode,
                        'value' => $this->rawData['fixed_price_modifier_value'][$key],
                    ];
                }
            }

            return $fixedPriceModifierData;
        }

        return null;
    }
}
