<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Synchronization;

use M2E\TikTokShop\Model\Template\Synchronization;

class SaveService
{
    private \M2E\TikTokShop\Model\Template\SynchronizationFactory $synchronizationFactory;
    private Synchronization\Repository $synchronizationRepository;
    private Synchronization\SnapshotBuilderFactory $syncSnapshotBuilderFactory;
    private Synchronization\BuilderFactory $builderFactory;
    private Synchronization\DiffFactory $diffFactory;
    private Synchronization\AffectedListingsProductsFactory $affectedProductsFactory;
    private Synchronization\ChangeProcessorFactory $changeProcessorFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Template\SynchronizationFactory $synchronizationFactory,
        Synchronization\ChangeProcessorFactory $changeProcessorFactory,
        Synchronization\AffectedListingsProductsFactory $affectedProductsFactory,
        Synchronization\DiffFactory $diffFactory,
        Synchronization\BuilderFactory $builderFactory,
        Synchronization\Repository $synchronizationRepository,
        Synchronization\SnapshotBuilderFactory $syncSnapshotBuilderFactory
    ) {
        $this->synchronizationFactory = $synchronizationFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->affectedProductsFactory = $affectedProductsFactory;
        $this->diffFactory = $diffFactory;
        $this->builderFactory = $builderFactory;
        $this->synchronizationRepository = $synchronizationRepository;
        $this->syncSnapshotBuilderFactory = $syncSnapshotBuilderFactory;
    }

    public function save(array $data)
    {
        $templateModel = $this->synchronizationFactory->create();

        if (empty($data['id'])) {
            $oldData = [];
        } else {
            $templateModel = $this->synchronizationRepository->get((int)$data['id']);
            $oldData = $this->makeSnapshot($templateModel);
        }

        $templateBuilder = $this->builderFactory->create();
        $template = $templateBuilder->build($templateModel, $data);
        if (empty($data['id'])) {
            $this->synchronizationRepository->create($template);
        } else {
            $this->synchronizationRepository->save($template);
        }

        $snapshotBuilder = $this->syncSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($template);

        $newData = $this->makeSnapshot($template);

        $diff = $this->diffFactory->create();

        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = $this->affectedProductsFactory->create();
        $affectedListingsProducts->setModel($template);

        $changeProcessor = $this->changeProcessorFactory->create();

        $changeProcessor->process(
            $diff,
            $affectedListingsProducts->getObjectsData(['id', 'status'])
        );

        return $template;
    }

    private function makeSnapshot($model)
    {
        $snapshotBuilder = $this->syncSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($model);

        return $snapshotBuilder->getSnapshot();
    }
}
