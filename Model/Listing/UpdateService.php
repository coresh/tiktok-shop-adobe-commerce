<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing;

use M2E\TikTokShop\Model\ResourceModel\Listing as ListingResource;
use M2E\TikTokShop\Model\Template\Description;
use M2E\TikTokShop\Model\Template\SellingFormat;
use M2E\TikTokShop\Model\Template\Synchronization;
use M2E\TikTokShop\Model\Template\Compliance;

class UpdateService
{
    private \M2E\TikTokShop\Model\TikTokShop\Listing\SnapshotBuilderFactory $listingSnapshotBuilderFactory;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private Description\Repository $descriptionTemplateRepository;
    private Description\SnapshotBuilderFactory $descriptionSnapshotBuilderFactory;
    private Description\DiffFactory $descriptionDiffFactory;
    private Description\ChangeProcessorFactory $descriptionChangeProcessorFactory;
    private SellingFormat\Repository $sellingFormatTemplateRepository;
    private SellingFormat\SnapshotBuilderFactory $sellingFormatSnapshotBuilderFactory;
    private SellingFormat\DiffFactory $sellingFormatDiffFactory;
    private SellingFormat\ChangeProcessorFactory $sellingFormatChangeProcessorFactory;
    private Synchronization\Repository $synchronizationTemplateRepository;
    private Synchronization\SnapshotBuilderFactory $synchronizationSnapshotBuilderFactory;
    private Synchronization\DiffFactory $synchronizationDiffFactory;
    private Synchronization\ChangeProcessorFactory $synchronizationChangeProcessorFactory;
    private Compliance\ChangeProcessorFactory $complianceChangeProcessorFactory;
    private Compliance\Repository $complianceTemplateRepository;
    private Compliance\SnapshotBuilderFactory $complianceSnapshotBuilderFactory;
    private Compliance\DiffFactory $complianceDiffFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\TikTokShop\Listing\SnapshotBuilderFactory $listingSnapshotBuilderFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        Description\Repository $descriptionTemplateRepository,
        Description\SnapshotBuilderFactory $descriptionSnapshotBuilderFactory,
        Description\DiffFactory $descriptionDiffFactory,
        Description\ChangeProcessorFactory $descriptionChangeProcessorFactory,
        SellingFormat\Repository $sellingFormatTemplateRepository,
        SellingFormat\SnapshotBuilderFactory $sellingFormatSnapshotBuilderFactory,
        SellingFormat\DiffFactory $sellingFormatDiffFactory,
        SellingFormat\ChangeProcessorFactory $sellingFormatChangeProcessorFactory,
        Synchronization\Repository $synchronizationTemplateRepository,
        Synchronization\SnapshotBuilderFactory $synchronizationSnapshotBuilderFactory,
        Synchronization\DiffFactory $synchronizationDiffFactory,
        Synchronization\ChangeProcessorFactory $synchronizationChangeProcessorFactory,
        Compliance\ChangeProcessorFactory $complianceChangeProcessorFactory,
        Compliance\Repository $complianceTemplateRepository,
        Compliance\SnapshotBuilderFactory $complianceSnapshotBuilderFactory,
        Compliance\DiffFactory $complianceDiffFactory
    ) {
        $this->listingSnapshotBuilderFactory = $listingSnapshotBuilderFactory;
        $this->listingRepository = $listingRepository;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->descriptionTemplateRepository = $descriptionTemplateRepository;
        $this->descriptionSnapshotBuilderFactory = $descriptionSnapshotBuilderFactory;
        $this->descriptionDiffFactory = $descriptionDiffFactory;
        $this->descriptionChangeProcessorFactory = $descriptionChangeProcessorFactory;
        $this->sellingFormatTemplateRepository = $sellingFormatTemplateRepository;
        $this->sellingFormatSnapshotBuilderFactory = $sellingFormatSnapshotBuilderFactory;
        $this->sellingFormatDiffFactory = $sellingFormatDiffFactory;
        $this->sellingFormatChangeProcessorFactory = $sellingFormatChangeProcessorFactory;
        $this->synchronizationTemplateRepository = $synchronizationTemplateRepository;
        $this->synchronizationSnapshotBuilderFactory = $synchronizationSnapshotBuilderFactory;
        $this->synchronizationDiffFactory = $synchronizationDiffFactory;
        $this->synchronizationChangeProcessorFactory = $synchronizationChangeProcessorFactory;
        $this->complianceChangeProcessorFactory = $complianceChangeProcessorFactory;
        $this->complianceTemplateRepository = $complianceTemplateRepository;
        $this->complianceSnapshotBuilderFactory = $complianceSnapshotBuilderFactory;
        $this->complianceDiffFactory = $complianceDiffFactory;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function update(\M2E\TikTokShop\Model\Listing $listing, array $post)
    {
        $isNeedProcessChangesSellingFormatTemplate = false;
        $isNeedProcessChangesDescriptionTemplate = false;
        $isNeedProcessChangesSynchronizationTemplate = false;
        $isNeedProcessChangesComplianceTemplate = false;

        $oldListingSnapshot = $this->makeListingSnapshot($listing);

        $newTemplateSellingFormatId = $post[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID] ?? null;
        if (
            $newTemplateSellingFormatId !== null
            && $listing->getTemplateSellingFormatId() !== (int)$newTemplateSellingFormatId
        ) {
            $listing->setTemplateSellingFormatId((int)$newTemplateSellingFormatId);
            $isNeedProcessChangesSellingFormatTemplate = true;
        }

        $newTemplateDescriptionId = $post[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID] ?? null;
        if (
            $newTemplateDescriptionId !== null
            && $listing->getTemplateDescriptionId() !== (int)$newTemplateDescriptionId
        ) {
            $listing->setTemplateDescriptionId((int)$newTemplateDescriptionId);
            $isNeedProcessChangesDescriptionTemplate = true;
        }

        $newTemplateSynchronizationId = $post[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID] ?? null;
        if (
            $newTemplateSynchronizationId !== null
            && $listing->getTemplateSynchronizationId() !== (int)$newTemplateSynchronizationId
        ) {
            $listing->setTemplateSynchronizationId((int)$newTemplateSynchronizationId);
            $isNeedProcessChangesSynchronizationTemplate = true;
        }

        $newTemplateComplianceId = $post[ListingResource::COLUMN_TEMPLATE_COMPLIANCE_ID] ?? null;
        if (
            $newTemplateComplianceId !== null
            && $listing->getTemplateComplianceId() !== (int)$newTemplateComplianceId
        ) {
            $listing->setTemplateComplianceId((int)$newTemplateComplianceId);
            $isNeedProcessChangesComplianceTemplate = true;
        }

        if (
            $isNeedProcessChangesDescriptionTemplate === false
            && $isNeedProcessChangesSellingFormatTemplate === false
            && $isNeedProcessChangesSynchronizationTemplate === false
            && $isNeedProcessChangesComplianceTemplate === false
        ) {
            return;
        }

        $this->listingRepository->save($listing);

        $newListingSnapshot = $this->makeListingSnapshot($listing);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($listing);

        if ($isNeedProcessChangesDescriptionTemplate) {
            $this->processChangeDescriptionTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_DESCRIPTION_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesSellingFormatTemplate) {
            $this->processChangeSellingFormatTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_SELLING_FORMAT_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesSynchronizationTemplate) {
            $this->processChangeSynchronizationTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_SYNCHRONIZATION_ID],
                $affectedListingsProducts
            );
        }

        if ($isNeedProcessChangesComplianceTemplate) {
            $this->processChangeComplianceTemplate(
                (int)$oldListingSnapshot[ListingResource::COLUMN_TEMPLATE_COMPLIANCE_ID],
                (int)$newListingSnapshot[ListingResource::COLUMN_TEMPLATE_COMPLIANCE_ID],
                $affectedListingsProducts
            );
        }
    }

    private function makeListingSnapshot(\M2E\TikTokShop\Model\Listing $listing): array
    {
        $snapshotBuilder = $this->listingSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($listing);

        return $snapshotBuilder->getSnapshot();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function processChangeDescriptionTemplate(
        int $oldId,
        int $newId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->descriptionTemplateRepository->get($oldId);
        $newTemplate = $this->descriptionTemplateRepository->get($newId);

        $oldTemplateData = $this->makeDescriptionTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeDescriptionTemplateSnapshot($newTemplate);

        $diff = $this->descriptionDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->descriptionChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(['id', 'status']);
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeDescriptionTemplateSnapshot(Description $descriptionTemplate): array
    {
        $snapshotBuilder = $this->descriptionSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($descriptionTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function processChangeSellingFormatTemplate(
        int $oldId,
        int $newId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->sellingFormatTemplateRepository->get($oldId);
        $newTemplate = $this->sellingFormatTemplateRepository->get($newId);

        $oldTemplateData = $this->makeSellingFormatTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeSellingFormatTemplateSnapshot($newTemplate);

        $diff = $this->sellingFormatDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->sellingFormatChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(['id', 'status']);
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeSellingFormatTemplateSnapshot(SellingFormat $sellingFormatTemplate): array
    {
        $snapshotBuilder = $this->sellingFormatSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($sellingFormatTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function processChangeSynchronizationTemplate(
        int $oldId,
        int $newId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->synchronizationTemplateRepository->get($oldId);
        $newTemplate = $this->synchronizationTemplateRepository->get($newId);

        $oldTemplateData = $this->makeSynchronizationTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeSynchronizationTemplateSnapshot($newTemplate);

        $diff = $this->synchronizationDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->synchronizationChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(['id', 'status']);
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeSynchronizationTemplateSnapshot(Synchronization $synchronizationTemplate): array
    {
        $snapshotBuilder = $this->synchronizationSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($synchronizationTemplate);

        return $snapshotBuilder->getSnapshot();
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    private function processChangeComplianceTemplate(
        int $oldId,
        int $newId,
        \M2E\TikTokShop\Model\TikTokShop\Listing\AffectedListingsProducts $affectedListingsProducts
    ) {
        $oldTemplate = $this->complianceTemplateRepository->get($oldId);
        $newTemplate = $this->complianceTemplateRepository->get($newId);

        $oldTemplateData = $this->makeComplianceTemplateSnapshot($oldTemplate);
        $newTemplateData = $this->makeComplianceTemplateSnapshot($newTemplate);

        $diff = $this->complianceDiffFactory->create();
        $diff->setOldSnapshot($oldTemplateData);
        $diff->setNewSnapshot($newTemplateData);

        $changeProcessor = $this->complianceChangeProcessorFactory->create();

        $affectedProducts = $affectedListingsProducts->getObjectsData(['id', 'status']);
        $changeProcessor->process($diff, $affectedProducts);
    }

    private function makeComplianceTemplateSnapshot(Compliance $complianceTemplate): array
    {
        $snapshotBuilder = $this->complianceSnapshotBuilderFactory->create();
        $snapshotBuilder->setModel($complianceTemplate);

        return $snapshotBuilder->getSnapshot();
    }
}
