<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Manufacturer;

use M2E\TikTokShop\Model\Channel\Manufacturer;

class Collection
{
    /** @var \M2E\TikTokShop\Model\Channel\Manufacturer[] */
    private array $manufacturers = [];

    public function add(\M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer): self
    {
        $this->manufacturers[$manufacturer->id] = $manufacturer;

        return $this;
    }

    public function has(?string $id): bool
    {
        return isset($this->manufacturers[$id]);
    }

    public function get(string $id): \M2E\TikTokShop\Model\Channel\Manufacturer
    {
        return $this->manufacturers[$id];
    }

    public function isEmpty(): bool
    {
        return empty($this->manufacturers);
    }

    /**
     * @return \M2E\TikTokShop\Model\Channel\Manufacturer[]
     */
    public function getAll(): array
    {
        return array_values($this->manufacturers);
    }

    // ----------------------------------------

    public static function createFromArray(array $data): self
    {
        $obj = new self();
        foreach ($data as $manufacturer) {
            $obj->add(Manufacturer::createFromArray($manufacturer));
        }

        return $obj;
    }

    public function toArray(): array
    {
        $manufacturers = [];
        foreach ($this->manufacturers as $manufacturer) {
            $manufacturers[] = $manufacturer->toArray();
        }

        return $manufacturers;
    }
}
