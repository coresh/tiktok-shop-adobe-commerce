<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Template\Description;

use M2E\TikTokShop\Model\Template\Description;

class SaveService
{
    private \M2E\TikTokShop\Model\Template\DescriptionFactory $descriptionFactory;
    private Description\Repository $descriptionRepository;
    private Description\SnapshotBuilderFactory $snapshotBuilderFactory;
    private Description\BuilderFactory $builderFactory;
    private Description\DiffFactory $diffFactory;
    private Description\AffectedListingsProductsFactory $affectedProductsFactory;
    private Description\ChangeProcessorFactory $changeProcessorFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Template\DescriptionFactory $descriptionFactory,
        Description\ChangeProcessorFactory $changeProcessorFactory,
        Description\AffectedListingsProductsFactory $affectedProductsFactory,
        Description\DiffFactory $diffFactory,
        Description\BuilderFactory $builderFactory,
        Description\Repository $descriptionRepository,
        Description\SnapshotBuilderFactory $snapshotBuilderFactory
    ) {
        $this->descriptionFactory = $descriptionFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->affectedProductsFactory = $affectedProductsFactory;
        $this->diffFactory = $diffFactory;
        $this->builderFactory = $builderFactory;
        $this->descriptionRepository = $descriptionRepository;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
    }

    public function save(array $data)
    {
        $templateModel = $this->descriptionFactory->create();

        if (empty($data['id'])) {
            $oldData = [];
        } else {
            $templateModel = $this->descriptionRepository->get((int)$data['id']);
            $oldData = $this->makeSnapshot($templateModel);
        }

        $templateBuilder = $this->builderFactory->create();
        $template = $templateBuilder->build($templateModel, $data);
        if (empty($data['id'])) {
            $this->descriptionRepository->create($template);
        } else {
            $this->descriptionRepository->save($template);
        }

        $snapshotBuilder = $this->snapshotBuilderFactory->create();
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
        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($model);

        return $snapshotBuilder->getSnapshot();
    }
}
