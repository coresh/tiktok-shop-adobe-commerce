<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Setup;

class InstallChecker
{
    private Repository $setupRepository;

    public function __construct(
        Repository $setupRepository
    ) {
        $this->setupRepository = $setupRepository;
    }

    public function isInstalled(): bool
    {
        return $this->setupRepository->isAlreadyInstalled();
    }
}
