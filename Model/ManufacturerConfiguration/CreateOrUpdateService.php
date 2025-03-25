<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ManufacturerConfiguration;

class CreateOrUpdateService
{
    private \M2E\TikTokShop\Model\ManufacturerConfiguration\Repository $manufacturerConfigurationRepository;
    private \M2E\TikTokShop\Model\ManufacturerConfigurationFactory $manufacturerConfigurationFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ManufacturerConfiguration\Repository $manufacturerConfigurationRepository,
        \M2E\TikTokShop\Model\ManufacturerConfigurationFactory $manufacturerConfigurationFactory
    ) {
        $this->manufacturerConfigurationRepository = $manufacturerConfigurationRepository;
        $this->manufacturerConfigurationFactory = $manufacturerConfigurationFactory;
    }

    public function execute(
        string $title,
        int $accountId,
        string $manufacturerId,
        array $responsiblePersonIds,
        ?int $id = null
    ): \M2E\TikTokShop\Model\ManufacturerConfiguration {
        if (empty($id)) {
            return $this->create(
                $title,
                $accountId,
                $manufacturerId,
                $responsiblePersonIds
            );
        }

        return $this->update(
            $id,
            $title,
            $accountId,
            $manufacturerId,
            $responsiblePersonIds
        );
    }

    private function create(
        string $title,
        int $accountId,
        string $manufacturerId,
        array $responsiblePersonIds
    ): \M2E\TikTokShop\Model\ManufacturerConfiguration {
        $manufacturerConfiguration = $this->manufacturerConfigurationFactory->create();
        $manufacturerConfiguration->setTitle($title);
        $manufacturerConfiguration->setAccountId($accountId);
        $manufacturerConfiguration->setManufacturerId($manufacturerId);
        $manufacturerConfiguration->setResponsiblePersonIds($responsiblePersonIds);

        $this->checkTitleUnique($manufacturerConfiguration);

        $this->manufacturerConfigurationRepository->create($manufacturerConfiguration);

        return $manufacturerConfiguration;
    }

    private function update(
        int $id,
        string $title,
        int $accountId,
        string $manufacturerId,
        array $responsiblePersonIds
    ): \M2E\TikTokShop\Model\ManufacturerConfiguration {
        $manufacturerConfiguration = $this->manufacturerConfigurationRepository->get($id);
        $manufacturerConfiguration->setTitle($title);
        $manufacturerConfiguration->setAccountId($accountId);
        $manufacturerConfiguration->setManufacturerId($manufacturerId);
        $manufacturerConfiguration->setResponsiblePersonIds($responsiblePersonIds);

        $this->checkTitleUnique($manufacturerConfiguration);

        $this->manufacturerConfigurationRepository->save($manufacturerConfiguration);

        return $manufacturerConfiguration;
    }

    private function checkTitleUnique(\M2E\TikTokShop\Model\ManufacturerConfiguration $manufacturerConfiguration): void
    {
        $isUniqueTitle = $this->manufacturerConfigurationRepository->isUniqueTitle(
            $manufacturerConfiguration->getTitle(),
            $manufacturerConfiguration->getAccountId(),
            $manufacturerConfiguration->getId()
        );

        if (!$isUniqueTitle) {
            throw new \M2E\TikTokShop\Model\Exception\Logic(
                (string) __('A manufacturer with this name already exists. Please choose a different name.')
            );
        }
    }
}
