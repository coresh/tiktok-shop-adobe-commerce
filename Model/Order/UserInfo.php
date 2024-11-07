<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order;

class UserInfo
{
    /** @var string */
    private $firstName;
    /** @var string|null */
    private $middleName;
    /** @var string */
    private $lastName;
    /** @var string|null */
    private $prefix;
    /** @var string|null */
    private $suffix;

    public function __construct(
        string $firstName,
        ?string $middleName,
        string $lastName,
        ?string $prefix,
        ?string $suffix
    ) {
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }
}
