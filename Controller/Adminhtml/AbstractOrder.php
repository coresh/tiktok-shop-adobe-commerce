<?php

namespace M2E\TikTokShop\Controller\Adminhtml;

abstract class AbstractOrder extends AbstractMain
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::sales');
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    protected function getProductOptionsDataFromPost(): array
    {
        $optionsData = $this->getRequest()->getParam('option_id');

        if ($optionsData === null || count($optionsData) == 0) {
            return [];
        }

        foreach ($optionsData as $optionId => $optionData) {
            $optionData = \M2E\TikTokShop\Helper\Json::decode($optionData);

            if (!isset($optionData['value_id']) || !isset($optionData['product_ids'])) {
                return [];
            }

            $optionsData[$optionId] = $optionData;
        }

        return $optionsData;
    }
}
