<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\License\Add;

class Request
{
    private string $domain;
    private string $directory;
    private string $email;
    private string $firstName;
    private string $lastName;
    private string $phone;
    private string $country;
    private string $city;
    private string $postalCode;

    public function __construct(
        string $domain,
        string $directory,
        string $email,
        string $firstName,
        string $lastName,
        string $phone,
        string $country,
        string $city,
        string $postalCode
    ) {
        $this->domain = $domain;
        $this->directory = $directory;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->country = $country;
        $this->city = $city;
        $this->postalCode = $postalCode;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
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
