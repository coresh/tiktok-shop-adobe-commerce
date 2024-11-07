<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Action;

use M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class ActionService
{
    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;
    private Action\Manual\Realtime\ListAction $realtimeListAction;
    private Action\Manual\Schedule\ListAction $scheduledListAction;
    private Action\Manual\Realtime\RelistAction $realtimeRelistAction;
    private Action\Manual\Schedule\RelistAction $scheduledRelistAction;
    private Action\Manual\Realtime\ReviseAction $realtimeReviseAction;
    private Action\Manual\Schedule\ReviseAction $scheduledReviseAction;
    private Action\Manual\Realtime\StopAction $realtimeStopAction;
    private Action\Manual\Schedule\StopAction $scheduledStopAction;
    private Action\Manual\Realtime\StopAndRemoveAction $realtimeStopAndRemoveAction;
    private Action\Manual\Schedule\StopAndRemoveAction $scheduledStopAndRemoveAction;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService,
        Action\Manual\Realtime\ListAction $realtimeListAction,
        Action\Manual\Schedule\ListAction $scheduledListAction,
        Action\Manual\Realtime\RelistAction $realtimeRelistAction,
        Action\Manual\Schedule\RelistAction $scheduledRelistAction,
        Action\Manual\Realtime\ReviseAction $realtimeReviseAction,
        Action\Manual\Schedule\ReviseAction $scheduledReviseAction,
        Action\Manual\Realtime\StopAction $realtimeStopAction,
        Action\Manual\Schedule\StopAction $scheduledStopAction,
        Action\Manual\Realtime\StopAndRemoveAction $realtimeStopAndRemoveAction,
        Action\Manual\Schedule\StopAndRemoveAction $scheduledStopAndRemoveAction
    ) {
        $this->listingLogService = $listingLogService;
        $this->realtimeListAction = $realtimeListAction;
        $this->scheduledListAction = $scheduledListAction;
        $this->realtimeRelistAction = $realtimeRelistAction;
        $this->scheduledRelistAction = $scheduledRelistAction;
        $this->realtimeReviseAction = $realtimeReviseAction;
        $this->scheduledReviseAction = $scheduledReviseAction;
        $this->realtimeStopAction = $realtimeStopAction;
        $this->scheduledStopAction = $scheduledStopAction;
        $this->realtimeStopAndRemoveAction = $realtimeStopAndRemoveAction;
        $this->scheduledStopAndRemoveAction = $scheduledStopAndRemoveAction;
    }

    // ----------------------------------------

    public function runList(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeListAction, []);
    }

    public function scheduleList(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledListAction, []);
    }

    // ----------------------------------------

    public function runRelist(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeRelistAction, []);
    }

    public function scheduleRelist(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledRelistAction, []);
    }

    // ----------------------------------------

    public function runRevise(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeReviseAction, []);
    }

    public function scheduleRevise(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledReviseAction, []);
    }

    // ----------------------------------------

    public function runStop(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeStopAction, []);
    }

    public function scheduleStop(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledStopAction, []);
    }

    // ----------------------------------------

    public function runStopAndRemove(array $products): array
    {
        return $this->processRealtime($products, $this->realtimeStopAndRemoveAction, ['remove' => true]);
    }

    public function scheduleStopAndRemove(array $products): array
    {
        return $this->createScheduleAction($products, $this->scheduledStopAndRemoveAction, ['remove' => true]);
    }

    // ----------------------------------------

    /**
     * @param \M2E\TikTokShop\Model\Product[] $products
     * @param \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Realtime\AbstractRealtime $processor
     * @param array $params
     *
     * @return array
     */
    private function processRealtime(
        array $products,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Realtime\AbstractRealtime $processor,
        array $params
    ): array {
        $logsActionId = $this->listingLogService->getNextActionId();
        if (empty($products)) {
            return ['result' => 'error', 'action_id' => $logsActionId];
        }

        $result = $processor->process($products, $params, $logsActionId);

        if ($result->isError()) {
            return ['result' => 'error', 'action_id' => $logsActionId];
        }

        if ($result->isWarning()) {
            return ['result' => 'warning', 'action_id' => $logsActionId];
        }

        return ['result' => 'success', 'action_id' => $logsActionId];
    }

    /**
     * @param \M2E\TikTokShop\Model\Product[] $products
     * @param \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Schedule\AbstractSchedule $processor
     * @param array $params
     *
     * @return array
     */
    private function createScheduleAction(
        array $products,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Schedule\AbstractSchedule $processor,
        array $params
    ): array {
        $logsActionId = $this->listingLogService->getNextActionId();
        if (empty($products)) {
            return ['result' => 'error', 'action_id' => $logsActionId];
        }

        $processor->process($products, $params, $logsActionId);

        return ['result' => 'success', 'action_id' => $logsActionId];
    }
}
