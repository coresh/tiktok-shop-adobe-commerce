<?php

namespace M2E\TikTokShop\Model\TikTokShop\Order;

class ShippingAddress extends \M2E\TikTokShop\Model\Order\ShippingAddress
{
    /**
     * @return array
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function getRawData()
    {
        $buyerName = $this->order->getBuyerName();
        $recipientName = $this->getData('recipient_name');

        return [
            'buyer_name' => $buyerName,
            'recipient_name' => $recipientName ? $recipientName : $buyerName,
            'email' => $this->getBuyerEmail(),
            'country_id' => $this->getData('country_code'),
            'region' => $this->getData('state'),
            'city' => $this->getData('city') ? $this->getData('city') : $this->getCountryName(),
            'postcode' => $this->getPostalCode(),
            'telephone' => $this->getPhone(),
            'company' => $this->getData('company'),
            'street' => $this->getStreet(),
        ];
    }

    protected function getBuyerEmail()
    {
        $email = $this->order->getBuyerEmail();

        if (stripos($email, 'Invalid Request') !== false || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = str_replace(' ', '-', strtolower($this->order->getBuyerUserId()));
            $email .= \M2E\Core\Model\Magento\Customer::FAKE_EMAIL_POSTFIX;
        }

        return $email;
    }

    protected function getPostalCode()
    {
        $postalCode = $this->getData('postal_code');

        if (stripos($postalCode, 'Invalid Request') !== false || $postalCode == '') {
            $postalCode = '0000';
        }

        return $postalCode;
    }

    protected function getPhone()
    {
        $phone = $this->getData('phone');

        if (stripos($phone, 'Invalid Request') !== false || $phone == '') {
            $phone = '0000000000';
        }

        return $phone;
    }

    protected function getStreet()
    {
        return $this->getData('street');
    }

    protected function isRegionOverrideRequired(): bool
    {
        return $this->order->getAccount()->getOrdersSettings()->isRegionOverrideRequired();
    }
}
