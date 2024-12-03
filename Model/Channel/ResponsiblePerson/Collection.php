<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\ResponsiblePerson;

class Collection
{
    /** @var \M2E\TikTokShop\Model\Channel\ResponsiblePerson[] */
    private array $responsiblePersons = [];

    public function add(\M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson): self
    {
        $this->responsiblePersons[$responsiblePerson->id] = $responsiblePerson;

        return $this;
    }

    public function has(?string $id): bool
    {
        return isset($this->responsiblePersons[$id]);
    }

    public function get(string $id): \M2E\TikTokShop\Model\Channel\ResponsiblePerson
    {
        return $this->responsiblePersons[$id];
    }

    public function isEmpty(): bool
    {
        return empty($this->responsiblePersons);
    }

    /**
     * @return \M2E\TikTokShop\Model\Channel\ResponsiblePerson[]
     */
    public function getAll(): array
    {
        return array_values($this->responsiblePersons);
    }

    public static function fromArray(array $data): self
    {
        $obj = new self();
        foreach ($data as $responsiblePerson) {
            $obj->add(\M2E\TikTokShop\Model\Channel\ResponsiblePerson::createFromArray($responsiblePerson));
        }

        return $obj;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->responsiblePersons as $responsiblePerson) {
            $result[] = $responsiblePerson->toArray();
        }

        return $result;
    }
}
