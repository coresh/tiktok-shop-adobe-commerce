<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual;

abstract class AbstractManual
{
    private int $logsActionId;
    private \M2E\TikTokShop\Model\Product\LockManagerFactory $lockManagerFactory;
    private \M2E\TikTokShop\Model\Product\ActionCalculator $calculator;
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\TikTokShop\Model\Product\ActionCalculator $calculator,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\Product\LockManagerFactory $lockManagerFactory
    ) {
        $this->lockManagerFactory = $lockManagerFactory;
        $this->calculator = $calculator;
        $this->listingLogService = $listingLogService;
    }

    // ----------------------------------------

    /**
     * @param \M2E\TikTokShop\Model\Product[] $listingsProducts
     * @param array $params
     * @param int $logsActionId
     *
     * @return Result
     */
    public function process(array $listingsProducts, array $params, int $logsActionId): Result
    {
        $this->logsActionId = $logsActionId;

        $listingsProducts = $this->checkLocking($listingsProducts);
        if (empty($listingsProducts)) {
            return Result::createError($this->getLogActionId());
        }

        $listingsProducts = $this->prepareOrFilterProducts($listingsProducts);
        if (empty($listingsProducts)) {
            return Result::createSuccess($this->getLogActionId());
        }

        $actions = $this->calculateActions($listingsProducts);
        if (empty($actions)) {
            return Result::createSuccess($this->getLogActionId());
        }

        return $this->processAction($actions, $params);
    }

    abstract protected function getAction(): int;

    /**
     * @param \M2E\TikTokShop\Model\Product[] $listingsProducts
     *
     * @return array
     */
    protected function prepareOrFilterProducts(array $listingsProducts): array
    {
        return $listingsProducts;
    }

    /**
     * @param \M2E\TikTokShop\Model\Product[] $products
     *
     * @return \M2E\TikTokShop\Model\Product\Action[]
     */
    private function calculateActions(array $products): array
    {
        $result = [];
        foreach ($products as $product) {
            $calculateAction = $this->calculateAction($product, $this->calculator);
            if ($calculateAction->isActionNothing()) {
                $this->logAboutSkipAction($product, $this->listingLogService);

                continue;
            }

            $result[] = $calculateAction;
        }

        return $result;
    }

    abstract protected function calculateAction(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Product\ActionCalculator $calculator
    ): \M2E\TikTokShop\Model\Product\Action;

    abstract protected function logAboutSkipAction(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Listing\LogService $logService
    ): void;

    /**
     * @param \M2E\TikTokShop\Model\Product\Action[] $actions
     * @param array $params
     *
     * @return Result
     */
    abstract protected function processAction(array $actions, array $params): Result;

    /**
     * @param \M2E\TikTokShop\Model\Product[] $listingsProducts
     *
     * @return \M2E\TikTokShop\Model\Product[]
     */
    private function checkLocking(array $listingsProducts): array
    {
        $result = [];
        foreach ($listingsProducts as $listingProduct) {
            $lockManager = $this->lockManagerFactory->create();
            $lockManager->setListingProduct($listingProduct)
                        ->setInitiator(\M2E\Core\Helper\Data::INITIATOR_USER)
                        ->setLogsActionId($this->logsActionId)
                        ->setLogsAction($this->getLogsAction());

            if ($lockManager->checkLocking()) {
                continue;
            }

            $result[] = $listingProduct;
        }

        return $result;
    }

    private function getLogsAction(): int
    {
        switch ($this->getAction()) {
            case \M2E\TikTokShop\Model\Product::ACTION_LIST:
                return \M2E\TikTokShop\Model\Listing\Log::ACTION_LIST_PRODUCT;

            case \M2E\TikTokShop\Model\Product::ACTION_RELIST:
                return \M2E\TikTokShop\Model\Listing\Log::ACTION_RELIST_PRODUCT;

            case \M2E\TikTokShop\Model\Product::ACTION_REVISE:
                return \M2E\TikTokShop\Model\Listing\Log::ACTION_REVISE_PRODUCT;

            case \M2E\TikTokShop\Model\Product::ACTION_STOP:
                return \M2E\TikTokShop\Model\Listing\Log::ACTION_STOP_PRODUCT;

            case \M2E\TikTokShop\Model\Product::ACTION_DELETE:
                return \M2E\TikTokShop\Model\Listing\Log::ACTION_DELETE_PRODUCT;
        }

        throw new \M2E\TikTokShop\Model\Exception\Logic('Unknown action.', ['action' => $this->getAction()]);
    }

    // ----------------------------------------

    protected function getLogActionId(): int
    {
        return $this->logsActionId;
    }
}
