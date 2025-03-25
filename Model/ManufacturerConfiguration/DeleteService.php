<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ManufacturerConfiguration;

class DeleteService
{
    /** @var \M2E\TikTokShop\Model\ManufacturerConfiguration\Repository */
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function byAccount(int $accountId): void
    {
        $manufacturerConfigurations = $this->repository->findAllByAccountId($accountId);
        foreach ($manufacturerConfigurations as $manufacturerConfiguration) {
            $this->repository->delete($manufacturerConfiguration);
        }
    }
}
