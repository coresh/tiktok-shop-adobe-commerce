<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel;

class Manufacturer
{
    public ?string $id;
    public string $name;
    public ?string $registeredTradeName;
    public string $email;
    public string $phoneCountryCode;
    public string $phoneLocalNumber;
    public string $address;

    public function __construct(
        ?string $id,
        string $name,
        ?string $registeredTradeName,
        string $email,
        string $phoneCountryCode,
        string $phoneLocalNumber,
        string $adress
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->registeredTradeName = $registeredTradeName;
        $this->email = $email;
        $this->phoneCountryCode = $phoneCountryCode;
        $this->phoneLocalNumber = $phoneLocalNumber;
        $this->address = $adress;
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['registeredTradeName'],
            $data['email'],
            $data['phoneCountryCode'],
            $data['phoneLocalNumber'],
            $data['adress']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'registeredTradeName' => $this->registeredTradeName,
            'email' => $this->email,
            'phoneCountryCode' => $this->phoneCountryCode,
            'phoneLocalNumber' => $this->phoneLocalNumber,
            'adress' => $this->address,
        ];
    }
}
