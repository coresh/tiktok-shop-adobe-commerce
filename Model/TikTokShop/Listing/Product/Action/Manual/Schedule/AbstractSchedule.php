<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Schedule;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Result;

abstract class AbstractSchedule extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\AbstractManual
{
    private \M2E\TikTokShop\Model\ScheduledAction\CreateService $scheduledActionCreateService;

    public function __construct(
        \M2E\TikTokShop\Model\ScheduledAction\CreateService $scheduledActionCreateService,
        \M2E\TikTokShop\Model\Product\ActionCalculator $calculator,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\Product\LockManagerFactory $lockManagerFactory
    ) {
        parent::__construct($calculator, $listingLogService, $lockManagerFactory);
        $this->scheduledActionCreateService = $scheduledActionCreateService;
    }

    protected function processAction(array $actions, array $params): Result
    {
        foreach ($actions as $action) {
            $this->createScheduleAction(
                $action,
                $params,
                $this->scheduledActionCreateService,
            );
        }

        return Result::createSuccess($this->getLogActionId());
    }

    protected function createScheduleAction(
        \M2E\TikTokShop\Model\Product\Action $action,
        array $params,
        \M2E\TikTokShop\Model\ScheduledAction\CreateService $createService
    ): void {
        $scheduledActionParams = [
            'params' => $params,
        ];

        $createService->create(
            $action->getProduct(),
            $this->getAction(),
            \M2E\TikTokShop\Model\Product::STATUS_CHANGER_USER,
            $scheduledActionParams,
            $action->getConfigurator()->getEnabledDataTypes(),
            true,
            $action->getConfigurator(),
            $action->getVariantSettings()
        );
    }
}
