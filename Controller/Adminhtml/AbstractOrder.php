<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml;

abstract class AbstractOrder extends AbstractMain
{
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('M2E_TikTokShop::sales');
    }

    protected function getProductOptionsDataFromPost(): array
    {
        $optionsData = $this->getRequest()->getParam('option_id');

        if ($optionsData === null || count($optionsData) == 0) {
            return [];
        }

        $result = [];
        foreach ($optionsData as $optionId => $optionData) {
            if (is_string($optionData)) {
                $optionData = [$optionData];
            }

            foreach ($optionData as $optionItem) {
                $optionItem = \M2E\Core\Helper\Json::decode($optionItem);

                if (!isset($optionItem['value_id']) || !isset($optionItem['product_ids'])) {
                    return [];
                }

                $result[$optionId][] = $optionItem;
            }
        }

        return $result;
    }
}
