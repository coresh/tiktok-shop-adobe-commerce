<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Order;

class DistrictsCollection
{
    private array $districts;

    private function __construct(array $districts)
    {
        $this->districts = $districts;
    }

    public static function createFromArray(array $districtsInfo): self
    {
        $districts = [];
        foreach ($districtsInfo as $districtInfo) {
            $districts[] = [
                'level' => $districtInfo['address_level_name'],
                'name' => $districtInfo['address_name'],
            ];
        }

        return new self($districts);
    }

    public function getDistricts(): array
    {
        return $this->districts;
    }

    public function tryFindLevelName(string $level): ?string
    {
        foreach ($this->districts as $district) {
            if (mb_strtolower($district['level']) === mb_strtolower($level)) {
                return (string)$district['name'];
            }
        }

        return null;
    }
}
