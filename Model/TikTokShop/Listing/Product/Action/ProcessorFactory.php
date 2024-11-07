<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class ProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;
    private LoggerFactory $loggerFactory;
    private \M2E\TikTokShop\Model\Product\LockManagerFactory $lockManagerFactory;

    public function __construct(
        LoggerFactory $loggerFactory,
        \M2E\TikTokShop\Model\Product\LockManagerFactory $lockManagerFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->loggerFactory = $loggerFactory;
        $this->lockManagerFactory = $lockManagerFactory;
    }

    private function create(
        string $processorClass,
        \M2E\TikTokShop\Model\Product $listingProduct,
        Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings,
        Logger $actionLogger,
        array $params,
        int $statusChanger
    ): AbstractProcessor {
        /** @var AbstractProcessor $obj */
        $obj = $this->objectManager->create($processorClass);

        $obj->setProduct($listingProduct);
        $obj->setActionConfigurator($configurator, $variantSettings);
        $obj->setStatusChanger($statusChanger);

        $obj->setActionLogger($actionLogger);

        $obj->setLockManager(
            $this->createLockManager(
                $listingProduct,
                $actionLogger,
            ),
        );

        $obj->setParams($params);

        return $obj;
    }

    public function createListProcessor(
        \M2E\TikTokShop\Model\Product $listingProduct,
        Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\ListAction\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_LIST_PRODUCT,
        );

        /** @var Type\ListAction\Processor */
        return $this->create(
            Type\ListAction\Processor::class,
            $listingProduct,
            $configurator,
            $variantSettings,
            $actionLogger,
            $params,
            $statusChanger,
        );
    }

    public function createReviseProcessor(
        \M2E\TikTokShop\Model\Product $listingProduct,
        Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\Revise\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_REVISE_PRODUCT,
        );

        /** @var Type\Revise\Processor */
        return $this->create(
            Type\Revise\Processor::class,
            $listingProduct,
            $configurator,
            $variantSettings,
            $actionLogger,
            $params,
            $statusChanger
        );
    }

    public function createRelistProcessor(
        \M2E\TikTokShop\Model\Product $listingProduct,
        Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\Relist\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_RELIST_PRODUCT,
        );

        /** @var Type\Relist\Processor */
        return $this->create(
            Type\Relist\Processor::class,
            $listingProduct,
            $configurator,
            $variantSettings,
            $actionLogger,
            $params,
            $statusChanger
        );
    }

    public function createDeleteProcessor(
        \M2E\TikTokShop\Model\Product $listingProduct,
        Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\Delete\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_REMOVE_PRODUCT,
        );

        /** @var Type\Delete\Processor */
        return $this->create(
            Type\Delete\Processor::class,
            $listingProduct,
            $configurator,
            $variantSettings,
            $actionLogger,
            $params,
            $statusChanger
        );
    }

    public function createStopProcessor(
        \M2E\TikTokShop\Model\Product $listingProduct,
        Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings,
        int $statusChanger,
        int $actionLogId,
        array $params
    ): Type\Stop\Processor {
        $actionLogger = $this->createActionLogger(
            $statusChanger,
            $actionLogId,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_STOP_PRODUCT,
        );

        /** @var Type\Stop\Processor */
        return $this->create(
            Type\Stop\Processor::class,
            $listingProduct,
            $configurator,
            $variantSettings,
            $actionLogger,
            $params,
            $statusChanger
        );
    }

    // ----------------------------------------

    private function createActionLogger(
        int $statusChanger,
        int $logActionId,
        int $logAction
    ): Logger {
        return $this->loggerFactory->create(
            $logActionId,
            $logAction,
            $this->getInitiatorByChanger($statusChanger),
        );
    }

    private function createLockManager(
        \M2E\TikTokShop\Model\Product $listingProduct,
        Logger $logger
    ): \M2E\TikTokShop\Model\Product\LockManager {
        $manager = $this->lockManagerFactory->create();

        $manager->setListingProduct($listingProduct);
        $manager->setInitiator($logger->getInitiator());
        $manager->setLogsActionId($logger->getActionId());
        $manager->setLogsAction($logger->getAction());

        return $manager;
    }

    private function getInitiatorByChanger(int $statusChanger): int
    {
        switch ($statusChanger) {
            case \M2E\TikTokShop\Model\Product::STATUS_CHANGER_UNKNOWN:
                return \M2E\TikTokShop\Helper\Data::INITIATOR_UNKNOWN;
            case \M2E\TikTokShop\Model\Product::STATUS_CHANGER_USER:
                return \M2E\TikTokShop\Helper\Data::INITIATOR_USER;
            default:
                return \M2E\TikTokShop\Helper\Data::INITIATOR_EXTENSION;
        }
    }
}
