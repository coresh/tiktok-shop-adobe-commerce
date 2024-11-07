<?php

namespace M2E\TikTokShop\Model\TikTokShop\Magento\Product\Rule\Condition;

class Product extends \M2E\TikTokShop\Model\Magento\Product\Rule\Condition\Product
{
    /**
     * @param mixed $validatedValue
     *
     * @return bool
     */
    public function validateAttribute($validatedValue)
    {
        if (is_array($validatedValue)) {
            $result = false;

            foreach ($validatedValue as $value) {
                $result = parent::validateAttribute($value);
                if ($result) {
                    break;
                }
            }

            return $result;
        }

        return parent::validateAttribute($validatedValue);
    }
}
