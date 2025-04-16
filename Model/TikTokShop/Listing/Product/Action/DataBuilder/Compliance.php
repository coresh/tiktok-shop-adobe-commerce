<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class Compliance extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\AbstractDataBuilder
{
    private \M2E\TikTokShop\Model\ManufacturerConfiguration\Repository $manufacturerConfigurationRepository;
    private \M2E\TikTokShop\Model\ManufacturerConfiguration\Mapper $manufacturerConfigurationMapper;

    public function __construct(
        \M2E\TikTokShop\Model\ManufacturerConfiguration\Mapper $manufacturerConfigurationMapper,
        \M2E\TikTokShop\Model\ManufacturerConfiguration\Repository $manufacturerConfigurationRepository,
        \M2E\Core\Helper\Magento\Attribute $magentoAttributeHelper
    ) {
        parent::__construct($magentoAttributeHelper);
        $this->manufacturerConfigurationRepository = $manufacturerConfigurationRepository;
        $this->manufacturerConfigurationMapper = $manufacturerConfigurationMapper;
    }

    public const NICK = 'compliance';

    private ?string $manufacturerId = null;
    private ?array $responsiblePersonIds = [];

    public function getBuilderData(): array
    {
        if ($this->getListingProduct()->getManufacturerConfigId() === null) {
            $this->manufacturerConfigurationMapper->execute($this->getListingProduct());
        }

        $manufacturerConfiguration = $this
            ->manufacturerConfigurationRepository
            ->find((int)$this->getListingProduct()->getManufacturerConfigId());

        if ($manufacturerConfiguration === null) {
            return [
                'manufacturer_id' => null,
                'responsible_person_ids' => null,
            ];
        }

        $this->manufacturerId = $manufacturerConfiguration->getManufacturerId();
        $this->responsiblePersonIds = $manufacturerConfiguration->getResponsiblePersonIds();

        $result['manufacturer_id'] = $this->manufacturerId;
        $result['responsible_person_ids'] = $this->responsiblePersonIds;

        return $result;
    }

    public function getMetaData(): array
    {
        return [
            self::NICK => [
                'online_manufacturer_id' => $this->manufacturerId,
                'online_responsible_person_ids' => $this->responsiblePersonIds,
            ],
        ];
    }
}
