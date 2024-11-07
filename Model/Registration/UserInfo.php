<?php

namespace M2E\TikTokShop\Model\Registration;

class UserInfo
{
    private string $email;
    private string $firstname;
    private string $lastname;
    private string $phone;
    private string $country;
    private string $city;
    private string $postalCode;

    public function __construct(
        $email,
        $firstname,
        $lastname,
        $phone,
        $country,
        $city,
        $postalCode
    ) {
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->phone = $phone;
        $this->country = $country;
        $this->city = $city;
        $this->postalCode = $postalCode;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }
}
