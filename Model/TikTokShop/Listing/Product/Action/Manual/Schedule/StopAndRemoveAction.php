<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Schedule;

class StopAndRemoveAction extends AbstractSchedule
{
    private \M2E\TikTokShop\Model\Product\RemoveHandler $removeHandler;

    public function __construct(
        \M2E\TikTokShop\Model\Product\RemoveHandler $removeHandler,
        \M2E\TikTokShop\Model\ScheduledAction\CreateService $scheduledActionCreateService,
        \M2E\TikTokShop\Model\Product\ActionCalculator $calculator,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\Product\LockManagerFactory $lockManagerFactory
    ) {
        parent::__construct($scheduledActionCreateService, $calculator, $listingLogService, $lockManagerFactory);
        $this->removeHandler = $removeHandler;
    }

    protected function getAction(): int
    {
        return \M2E\TikTokShop\Model\Product::ACTION_DELETE;
    }

    protected function prepareOrFilterProducts(array $listingsProducts): array
    {
        $result = [];
        foreach ($listingsProducts as $listingProduct) {
            if ($listingProduct->isRetirable()) {
                $result[] = $listingProduct;

                continue;
            }

            $this->removeHandler->process($listingProduct, \M2E\TikTokShop\Helper\Data::INITIATOR_USER);
        }

        return $result;
    }

    protected function calculateAction(\M2E\TikTokShop\Model\Product $product, \M2E\TikTokShop\Model\Product\ActionCalculator $calculator): \M2E\TikTokShop\Model\Product\Action
    {
        return \M2E\TikTokShop\Model\Product\Action::createStop($product);
    }

    protected function logAboutSkipAction(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Listing\LogService $logService
    ): void {
    }
}
