<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\StopQueue;

class CreateService
{
    private \M2E\TikTokShop\Model\StopQueueFactory $stopQueueFactory;
    private Repository $repository;
    private \M2E\TikTokShop\Helper\Module\Exception $helperException;
    private \M2E\TikTokShop\Helper\Module\Logger $logger;

    public function __construct(
        \M2E\TikTokShop\Model\StopQueueFactory $stopQueueFactory,
        \M2E\TikTokShop\Model\StopQueue\Repository $repository,
        \M2E\TikTokShop\Helper\Module\Exception $helperException,
        \M2E\TikTokShop\Helper\Module\Logger $logger
    ) {
        $this->stopQueueFactory = $stopQueueFactory;
        $this->repository = $repository;
        $this->helperException = $helperException;
        $this->logger = $logger;
    }

    public function create(\M2E\TikTokShop\Model\Product $listingProduct): void
    {
        if (!$listingProduct->isStoppable()) {
            return;
        }

        try {
            $stopQueue = $this->stopQueueFactory->create();
            $stopQueue->create(
                $listingProduct->getAccount()->getServerHash(),
                $listingProduct->getShop()->getShopId(),
                $listingProduct->getTTSProductId(),
            );
            $this->repository->create($stopQueue);
        } catch (\Throwable $exception) {
            $sku = $listingProduct->getFirstVariant()->getOnlineSku();

            $this->logger->process(
                sprintf(
                    'Product [Listing Product ID: %s, SKU %s] was not added to stop queue because of the error: %s',
                    $listingProduct->getId(),
                    $sku,
                    $exception->getMessage()
                ),
                'Product was not added to stop queue'
            );

            $this->helperException->process($exception);
        }
    }
}
