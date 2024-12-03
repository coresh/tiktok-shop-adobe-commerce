<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template;

class ComplianceFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function createEmpty(): Compliance
    {
        return $this->objectManager->create(Compliance::class);
    }

    public function create(
        \M2E\TikTokShop\Model\Account $account,
        string $title,
        string $manufacturerId,
        string $responsiblePersonId
    ): Compliance {
        $model = $this->createEmpty();
        $model->create(
            $account->getId(),
            $title,
            $manufacturerId,
            $responsiblePersonId
        );

        return $model;
    }
}
