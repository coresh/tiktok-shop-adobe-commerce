<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Form\Element;

class ShippingProviderMapping extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    public function getAccount(): \M2E\TikTokShop\Model\Account
    {
        return $this->getData('account');
    }

    public function getExistShippingProviderMapping(): array
    {
        return (array)$this->getData('exist_shipping_provider_mapping');
    }
}
