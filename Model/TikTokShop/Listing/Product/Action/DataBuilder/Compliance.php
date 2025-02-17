<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

class Compliance extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder\AbstractDataBuilder
{
    public const NICK = 'compliance';

    private ?string $manufacturerId = null;
    private ?array $responsiblePersonIds = [];

    public function getBuilderData(): array
    {
        $listing = $this->getListingProduct()->getListing();

        $result = [
            'manufacturer_id' => null,
            'responsible_person_ids' => null,
        ];

        if (!$listing->hasTemplateCompliance()) {
            return $result;
        }

        $policy = $listing->getTemplateCompliance();

        $this->manufacturerId = $policy->getManufacturerId();
        $this->responsiblePersonIds = $policy->getResponsiblePersonIds();

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
