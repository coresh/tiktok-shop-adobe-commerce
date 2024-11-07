<?php

namespace M2E\TikTokShop\Model\Magento\Product\Variation;

class Cache extends \M2E\TikTokShop\Model\Magento\Product\Variation
{
    public function getVariationsTypeStandard()
    {
        $params = [
            'virtual_attributes' => $this->getMagentoProduct()->getVariationVirtualAttributes(),
            'filter_attributes' => $this->getMagentoProduct()->getVariationFilterAttributes(),
            'is_ignore_virtual_attributes' => $this->getMagentoProduct()->isIgnoreVariationVirtualAttributes(),
            'is_ignore_filter_attributes' => $this->getMagentoProduct()->isIgnoreVariationFilterAttributes(),
        ];

        return $this->getMethodData(__FUNCTION__, $params);
    }

    public function getVariationsTypeRaw()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    public function getTitlesVariationSet()
    {
        return $this->getMethodData(__FUNCTION__);
    }

    protected function getMethodData($methodName, $params = null)
    {
        if ($this->getMagentoProduct() === null) {
            throw new \M2E\TikTokShop\Model\Exception('Magento Product was not set.');
        }

        $cacheKey = [
            __CLASS__,
            $methodName,
        ];

        if ($params !== null) {
            $cacheKey[] = $params;
        }

        $cacheResult = $this->getMagentoProduct()->getCacheValue($cacheKey);

        if ($this->getMagentoProduct()->isCacheEnabled() && $cacheResult !== null) {
            return $cacheResult;
        }

        $data = parent::$methodName();

        if (!$this->getMagentoProduct()->isCacheEnabled()) {
            return $data;
        }

        return $this->getMagentoProduct()->setCacheValue($cacheKey, $data);
    }
}
