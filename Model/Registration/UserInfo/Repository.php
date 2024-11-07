<?php

namespace M2E\TikTokShop\Model\Registration\UserInfo;

class Repository
{
    private \M2E\TikTokShop\Model\Registry\Manager $register;

    public function __construct(
        \M2E\TikTokShop\Model\Registry\Manager $register
    ) {
        $this->register = $register;
    }

    public function get(): ?\M2E\TikTokShop\Model\Registration\UserInfo
    {
        $data = $this->register->getValueFromJson('/registration/user_info/');

        if (empty($data)) {
            return null;
        }

        return new \M2E\TikTokShop\Model\Registration\UserInfo(
            $data['email'],
            $data['firstname'],
            $data['lastname'],
            $data['phone'],
            $data['country'],
            $data['city'],
            $data['postal_code']
        );
    }

    public function save(\M2E\TikTokShop\Model\Registration\UserInfo $info): void
    {
        $data = [
            'email' => $info->getEmail(),
            'firstname' => $info->getFirstname(),
            'lastname' => $info->getLastname(),
            'phone' => $info->getPhone(),
            'country' => $info->getCountry(),
            'city' => $info->getCity(),
            'postal_code' => $info->getPostalCode(),
        ];

        $this->register->setValue('/registration/user_info/', $data);
    }
}
