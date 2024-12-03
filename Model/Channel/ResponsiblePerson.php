<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel;

class ResponsiblePerson
{
    public ?string $id;
    public string $name;
    public string $email;
    public string $phoneCountryCode;
    public string $phoneLocalNumber;
    public string $streetAddressLine1;
    public ?string $streetAddressLine2;
    public ?string $district;
    public string $city;
    public string $province;
    public string $postalCode;
    public string $country;

    public function __construct(
        ?string $id,
        string $name,
        string $email,
        string $phoneCountryCode,
        string $phoneLocalNumber,
        string $streetAddressLine1,
        ?string $streetAddressLine2,
        ?string $district,
        string $city,
        string $province,
        string $postalCode,
        string $country
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phoneCountryCode = $phoneCountryCode;
        $this->phoneLocalNumber = $phoneLocalNumber;
        $this->streetAddressLine1 = $streetAddressLine1;
        $this->streetAddressLine2 = $streetAddressLine2;
        $this->district = $district;
        $this->city = $city;
        $this->province = $province;
        $this->postalCode = $postalCode;
        $this->country = $country;
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['email'],
            $data['phoneCountryCode'],
            $data['phoneLocalNumber'],
            $data['streetAddressLine1'],
            $data['streetAddressLine2'],
            $data['district'],
            $data['city'],
            $data['province'],
            $data['postalCode'],
            $data['country']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phoneCountryCode' => $this->phoneCountryCode,
            'phoneLocalNumber' => $this->phoneLocalNumber,
            'streetAddressLine1' => $this->streetAddressLine1,
            'streetAddressLine2' => $this->streetAddressLine2,
            'district' => $this->district,
            'city' => $this->city,
            'province' => $this->province,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
        ];
    }
}
