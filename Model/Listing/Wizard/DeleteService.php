<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\Wizard;

class DeleteService
{
    private const LIFETIME_ONE_WEEK_IN_DAYS = 7;

    /** @var \M2E\TikTokShop\Model\Listing\Wizard\Repository */
    private Repository $wizardRepository;

    public function __construct(Repository $wizardRepository)
    {
        $this->wizardRepository = $wizardRepository;
    }

    public function removeOld(): void
    {
        $borderDate = \M2E\Core\Helper\Date::createCurrentGmt()
                                                 ->modify(sprintf('- %d days', self::LIFETIME_ONE_WEEK_IN_DAYS));

        foreach ($this->wizardRepository->findOldCompleted($borderDate) as $wizard) {
            $this->wizardRepository->remove($wizard);
        }
    }

    public function removeByListing(\M2E\TikTokShop\Model\Listing $listing): void
    {
        foreach ($this->wizardRepository->findWizardsByListing($listing) as $wizard) {
            $this->wizardRepository->remove($wizard);
        }
    }
}
