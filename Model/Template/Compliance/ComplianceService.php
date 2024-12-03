<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Compliance;

class ComplianceService
{
    private const CACHE_KEY_MANUFACTURERS = 'compliance_manufacturers';
    private const CACHE_KEY_RESPONSIBLE_PERSONS = 'compliance_responsible_persons';
    private const CACHE_LIFETIME_THIRTY_MINUTES = 1800;

    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;
    private \M2E\TikTokShop\Model\Channel\ManufacturerService $manufacturerService;
    private \M2E\TikTokShop\Model\Channel\ResponsiblePersonService $responsiblePersonService;

    public function __construct(
        \M2E\TikTokShop\Model\Channel\ManufacturerService $manufacturerService,
        \M2E\TikTokShop\Model\Channel\ResponsiblePersonService $responsiblePersonService,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->cache = $cache;
        $this->manufacturerService = $manufacturerService;
        $this->responsiblePersonService = $responsiblePersonService;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer
     *
     * @return \M2E\TikTokShop\Model\Channel\Manufacturer
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData
     */
    public function createManufacturer(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer
    ): \M2E\TikTokShop\Model\Channel\Manufacturer {
        $manufacturer = $this->manufacturerService->create($account, $manufacturer);

        $this->clearCache($this->createCacheKey($account, self::CACHE_KEY_MANUFACTURERS));

        return $manufacturer;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer
     *
     * @return void
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData
     */
    public function updateManufacturer(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer
    ): void {
        $this->manufacturerService->update($account, $manufacturer);

        $this->clearCache($this->createCacheKey($account, self::CACHE_KEY_MANUFACTURERS));
    }

    public function findManufacturerById(
        \M2E\TikTokShop\Model\Account $account,
        string $id
    ): ?\M2E\TikTokShop\Model\Channel\Manufacturer {
        $collection = $this->getAllManufacturers($account, false);
        if ($collection->has($id)) {
            return $collection->get($id);
        }

        return null;
    }

    public function getAllManufacturers(
        \M2E\TikTokShop\Model\Account $account,
        bool $force
    ): \M2E\TikTokShop\Model\Channel\Manufacturer\Collection {
        if (!$force) {
            $cachedData = $this->fromCache($this->createCacheKey($account, self::CACHE_KEY_MANUFACTURERS));
            if ($cachedData !== null) {
                return \M2E\TikTokShop\Model\Channel\Manufacturer\Collection::createFromArray($cachedData);
            }
        }

        $manufacturerCollection = $this->manufacturerService->retrieve($account);
        if ($manufacturerCollection->isEmpty()) {
            return $manufacturerCollection;
        }

        $this->toCache(
            $manufacturerCollection->toArray(),
            $this->createCacheKey($account, self::CACHE_KEY_MANUFACTURERS)
        );

        return $manufacturerCollection;
    }

    // ----------------------------------------

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
     *
     * @return \M2E\TikTokShop\Model\Channel\ResponsiblePerson
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData
     */
    public function createResponsiblePerson(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
    ): \M2E\TikTokShop\Model\Channel\ResponsiblePerson {
        $responsiblePerson = $this->responsiblePersonService->create($account, $responsiblePerson);
        $this->clearCache($this->createCacheKey($account, self::CACHE_KEY_RESPONSIBLE_PERSONS));

        return $responsiblePerson;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
     *
     * @return void
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\TikTokShop\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData
     */
    public function updateResponsiblePerson(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
    ): void {
        $this->responsiblePersonService->update($account, $responsiblePerson);

        $this->clearCache($this->createCacheKey($account, self::CACHE_KEY_RESPONSIBLE_PERSONS));
    }

    public function getAllResponsiblePersons(
        \M2E\TikTokShop\Model\Account $account,
        bool $force
    ): \M2E\TikTokShop\Model\Channel\ResponsiblePerson\Collection {
        if (!$force) {
            $cachedData = $this->fromCache($this->createCacheKey($account, self::CACHE_KEY_RESPONSIBLE_PERSONS));
            if ($cachedData !== null) {
                return \M2E\TikTokShop\Model\Channel\ResponsiblePerson\Collection::fromArray($cachedData);
            }
        }

        $responsiblePersonsCollection = $this->responsiblePersonService->retrieve($account);
        if ($responsiblePersonsCollection->isEmpty()) {
            return $responsiblePersonsCollection;
        }

        $this->toCache(
            $responsiblePersonsCollection->toArray(),
            $this->createCacheKey($account, self::CACHE_KEY_RESPONSIBLE_PERSONS)
        );

        return $responsiblePersonsCollection;
    }

    public function findResponsiblePersonById(
        \M2E\TikTokShop\Model\Account $account,
        string $id
    ): ?\M2E\TikTokShop\Model\Channel\ResponsiblePerson {
        $collection = $this->getAllResponsiblePersons($account, false);
        if ($collection->has($id)) {
            return $collection->get($id);
        }

        return null;
    }

    // ----------------------------------------

    private function createCacheKey(\M2E\TikTokShop\Model\Account $account, $key): string
    {
        return $key . $account->getId();
    }

    private function clearCache(string $key): void
    {
        $this->cache->removeValue($key);
    }

    private function toCache(array $data, string $key): void
    {
        $this->cache->setValue($key, $data, [], self::CACHE_LIFETIME_THIRTY_MINUTES);
    }

    private function fromCache(string $key): ?array
    {
        return $this->cache->getValue($key);
    }
}
