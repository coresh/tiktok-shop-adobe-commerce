<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Compliance;

class SaveService
{
    private \M2E\TikTokShop\Model\Template\ComplianceFactory $complianceFactory;
    private \M2E\TikTokShop\Model\Template\Compliance\Repository $complianceRepository;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Template\Compliance\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \M2E\TikTokShop\Model\Template\Compliance\DiffFactory $diffFactory;
    private \M2E\TikTokShop\Model\Template\Compliance\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \M2E\TikTokShop\Model\Template\Compliance\ChangeProcessorFactory $changeProcessorFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Model\Template\ComplianceFactory $complianceFactory,
        \M2E\TikTokShop\Model\Template\Compliance\Repository $complianceRepository,
        \M2E\TikTokShop\Model\Template\Compliance\SnapshotBuilderFactory $snapshotBuilderFactory,
        \M2E\TikTokShop\Model\Template\Compliance\DiffFactory $diffFactory,
        \M2E\TikTokShop\Model\Template\Compliance\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \M2E\TikTokShop\Model\Template\Compliance\ChangeProcessorFactory $changeProcessorFactory
    ) {
        $this->accountRepository = $accountRepository;
        $this->complianceFactory = $complianceFactory;
        $this->complianceRepository = $complianceRepository;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->diffFactory = $diffFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
    }

    public function save(array $data): \M2E\TikTokShop\Model\Template\Compliance
    {
        if (empty($data['id'])) {
            $oldData = [];
            $compliance = $this->create($data);
        } else {
            $templateModel = $this->complianceRepository->get((int)$data['id']);
            $oldData = $this->makeSnapshot($templateModel);

            $compliance = $this->update($data);
        }

        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($compliance);

        $newData = $this->makeSnapshot($compliance);

        $diff = $this->diffFactory->create();

        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($compliance);

        $changeProcessor = $this->changeProcessorFactory->create();

        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );

        return $compliance;
    }

    private function create(array $data): \M2E\TikTokShop\Model\Template\Compliance
    {
        $account = $this->accountRepository->get((int)$data['account_id']);

        $compliance = $this->complianceFactory->create(
            $account,
            $data['title'],
            $data['manufacturer_id'],
            $data['responsible_person_id']
        );
        $this->complianceRepository->create($compliance);

        return $compliance;
    }

    private function update(array $data): \M2E\TikTokShop\Model\Template\Compliance
    {
        $compliance = $this->complianceRepository->get((int)$data['id']);

        $compliance->setTitle($data['title'])
                   ->setManufacturerId($data['manufacturer_id'])
                   ->setResponsiblePersonId($data['responsible_person_id']);

        $this->complianceRepository->save($compliance);

        return $compliance;
    }

    private function makeSnapshot($model)
    {
        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($model);

        return $snapshotBuilder->getSnapshot();
    }
}
