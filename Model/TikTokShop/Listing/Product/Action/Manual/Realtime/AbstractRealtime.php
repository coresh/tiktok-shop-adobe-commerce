<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Realtime;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Result;

abstract class AbstractRealtime extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\AbstractManual
{
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Dispatcher $actionDispatcher;

    public function __construct(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Dispatcher $actionDispatcher,
        \M2E\TikTokShop\Model\Product\ActionCalculator $calculator,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        \M2E\TikTokShop\Model\Product\LockManagerFactory $lockManagerFactory
    ) {
        parent::__construct($calculator, $listingLogService, $lockManagerFactory);
        $this->actionDispatcher = $actionDispatcher;
    }

    protected function processAction(array $actions, array $params): Result
    {
        $params['logs_action_id'] = $this->getLogActionId();

        $packageCollection = new \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\PackageCollection();
        foreach ($actions as $action) {
            $packageCollection->add($action->getProduct(), $action->getConfigurator(), $action->getVariantSettings());
        }

        $result = $this->actionDispatcher->process(
            $this->getAction(),
            $packageCollection,
            $params,
            \M2E\TikTokShop\Model\Product::STATUS_CHANGER_USER,
        );

        if ($result === \M2E\Core\Helper\Data::STATUS_ERROR) {
            return Result::createError($this->getLogActionId());
        }

        if ($result === \M2E\Core\Helper\Data::STATUS_WARNING) {
            return Result::createWarning($this->getLogActionId());
        }

        return Result::createSuccess($this->getLogActionId());
    }
}
