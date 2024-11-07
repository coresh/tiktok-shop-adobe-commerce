<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Other;

class DeleteService
{
    /** @var \M2E\TikTokShop\Model\Listing\Other\Repository */
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function process(\M2E\TikTokShop\Model\Listing\Other $other): void
    {
        $this->repository->remove($other);
    }
}
