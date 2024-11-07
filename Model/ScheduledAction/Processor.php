<?php

namespace M2E\TikTokShop\Model\ScheduledAction;

use M2E\TikTokShop\Model\ResourceModel\ScheduledAction\Collection as ScheduledActionCollection;
use M2E\TikTokShop\Model\ResourceModel\ScheduledAction\CollectionFactory as ScheduledActionCollectionFactory;
use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator;

class Processor
{
    private const LIST_PRIORITY = 25;
    private const RELIST_PRIORITY = 125;
    private const STOP_PRIORITY = 1000;
    private const REVISE_VARIANTS_PRIORITY = 500;
    private const REVISE_TITLE_PRIORITY = 50;
    private const REVISE_DESCRIPTION_PRIORITY = 50;
    private const REVISE_IMAGES_PRIORITY = 50;
    private const REVISE_CATEGORIES_PRIORITY = 50;
    private const REVISE_OTHER_PRIORITY = 50;

    private \M2E\TikTokShop\Model\Config\Manager $config;
    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private ScheduledActionCollectionFactory $scheduledActionCollectionFactory;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Dispatcher $actionDispatcher;
    /** @var \M2E\TikTokShop\Model\ScheduledAction\Repository */
    private Repository $scheduledActionRepository;

    public function __construct(
        \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\TikTokShop\Model\Config\Manager $config,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        ScheduledActionCollectionFactory $scheduledActionCollectionFactory,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Dispatcher $actionDispatcher
    ) {
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
        $this->scheduledActionCollectionFactory = $scheduledActionCollectionFactory;
        $this->exceptionHelper = $exceptionHelper;
        $this->actionDispatcher = $actionDispatcher;
        $this->scheduledActionRepository = $scheduledActionRepository;
    }

    public function process(): void
    {
        $limit = $this->calculateActionsCountLimit();
        if ($limit === 0) {
            return;
        }

        $scheduledActions = $this->getScheduledActionsForProcessing($limit);
        if (empty($scheduledActions)) {
            return;
        }

        foreach ($scheduledActions as $scheduledAction) {
            try {
                $listingProduct = $scheduledAction->getListingProduct();
                $additionalData = $scheduledAction->getAdditionalData();
                $statusChanger = $scheduledAction->getStatusChanger();
            } catch (\M2E\TikTokShop\Model\Exception\Logic $e) {
                $this->exceptionHelper->process($e);

                $this->scheduledActionRepository->remove($scheduledAction);

                continue;
            }

            $packageCollection = new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\PackageCollection();
            $packageCollection->add(
                $listingProduct,
                $scheduledAction->getConfigurator(),
                $scheduledAction->getVariantsSettings(),
            );

            $this->actionDispatcher->process(
                $scheduledAction->getActionType(),
                $packageCollection,
                $additionalData['params'] ?? [],
                $statusChanger
            );

            $this->scheduledActionRepository->remove($scheduledAction);
        }
    }

    private function calculateActionsCountLimit(): int
    {
        $maxAllowedActionsCount = (int)$this->config->getGroupValue(
            '/listing/product/scheduled_actions/',
            'max_prepared_actions_count'
        );

        if ($maxAllowedActionsCount <= 0) {
            return 0;
        }

        return $maxAllowedActionsCount;
    }

    /**
     * @return \M2E\TikTokShop\Model\ScheduledAction[]
     */
    private function getScheduledActionsForProcessing(int $limit): array
    {
        $connection = $this->resourceConnection->getConnection();

        $unionSelect = $connection->select()->union([
            $this->getListScheduledActionsPreparedCollection()->getSelect(),
            $this->getRelistScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseVariantsScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseTitleScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseDescriptionScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseImagesScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseCategoriesScheduledActionsPreparedCollection()->getSelect(),
            $this->getReviseOtherScheduledActionsPreparedCollection()->getSelect(),
            $this->getStopScheduledActionsPreparedCollection()->getSelect(),
            $this->getDeleteScheduledActionsPreparedCollection()->getSelect(),
        ]);

        $unionSelect->order(['coefficient DESC']);
        $unionSelect->order(['create_date ASC']);

        $unionSelect->distinct(true);
        $unionSelect->limit($limit);

        $scheduledActionsData = $unionSelect->query()->fetchAll();
        if (empty($scheduledActionsData)) {
            return [];
        }

        $scheduledActionsIds = [];
        foreach ($scheduledActionsData as $scheduledActionData) {
            $scheduledActionsIds[] = $scheduledActionData['id'];
        }

        return $this->scheduledActionRepository->getByIds($scheduledActionsIds);
    }

    // ---------------------------------------

    private function getListScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory->create()->getScheduledActionsPreparedCollection(
            self::LIST_PRIORITY,
            \M2E\TikTokShop\Model\Product::ACTION_LIST
        );
    }

    private function getRelistScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::RELIST_PRIORITY,
            \M2E\TikTokShop\Model\Product::ACTION_RELIST
        );

        return $collection;
    }

    private function getReviseVariantsScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_VARIANTS_PRIORITY,
            \M2E\TikTokShop\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_VARIANTS);

        return $collection;
    }

    private function getReviseTitleScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_TITLE_PRIORITY,
            \M2E\TikTokShop\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_TITLE);

        return $collection;
    }

    private function getReviseDescriptionScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_DESCRIPTION_PRIORITY,
            \M2E\TikTokShop\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_DESCRIPTION);

        return $collection;
    }

    private function getReviseImagesScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_IMAGES_PRIORITY,
            \M2E\TikTokShop\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_IMAGES);

        return $collection;
    }

    private function getReviseCategoriesScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_CATEGORIES_PRIORITY,
            \M2E\TikTokShop\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_CATEGORIES);

        return $collection;
    }

    private function getReviseOtherScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        $collection = $this->scheduledActionCollectionFactory->create();

        $collection->getScheduledActionsPreparedCollection(
            self::REVISE_OTHER_PRIORITY,
            \M2E\TikTokShop\Model\Product::ACTION_REVISE
        );
        $collection->addTagFilter(Configurator::DATA_TYPE_OTHER);

        return $collection;
    }

    private function getStopScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory
            ->create()
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                \M2E\TikTokShop\Model\Product::ACTION_STOP
            );
    }

    private function getDeleteScheduledActionsPreparedCollection(): ScheduledActionCollection
    {
        return $this->scheduledActionCollectionFactory
            ->create()
            ->getScheduledActionsPreparedCollection(
                self::STOP_PRIORITY,
                \M2E\TikTokShop\Model\Product::ACTION_DELETE
            );
    }
}
