<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Change;

class ShippingProcessor
{
    private const MAX_CHANGE_FOR_PROCESS = 50;

    private \M2E\TikTokShop\Model\Order\Change\ShippingProcessor\ChangeProcessor $changeProcessor;
    /** @var \M2E\TikTokShop\Model\Order\Change\Repository */
    private Repository $changeRepository;
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Change\Repository $changeRepository,
        \M2E\TikTokShop\Model\Order\Change\ShippingProcessor\ChangeProcessor $changeProcessor,
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository
    ) {
        $this->changeProcessor = $changeProcessor;
        $this->changeRepository = $changeRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function process(\M2E\TikTokShop\Model\Account $account): void
    {
        $changes = $this->changeRepository->findShippingReadyForProcess($account, self::MAX_CHANGE_FOR_PROCESS);
        foreach ($changes as $change) {
            $change->incrementAttempts();

            $this->changeRepository->save($change);

            $result = $this->changeProcessor->process($account, $change);

            $this->changeRepository->delete($change);

            if ($result->isSkipped) {
                continue;
            }

            $order = $change->getOrder();

            if (!$result->isSuccess) {
                $this->processError($order, $result);

                continue;
            }

            $this->processSuccess($order, $result);
        }
    }

    private function processError(
        \M2E\TikTokShop\Model\Order $order,
        \M2E\TikTokShop\Model\Order\Change\ShippingProcessor\ChangeResult $changeResult
    ): void {
        $errors = $changeResult->messages;

        $reason = array_shift($errors);
        $order->addErrorLog(
            'Channel Order was not updated with the tracking number "%tracking%" for "%carrier%". ' .
            'Reason: %reason%',
            [
                'reason' => $reason->getText(),
                '!tracking' => $changeResult->trackingNumber,
                '!carrier' => $changeResult->shippingProviderName,
            ]
        );

        foreach ($errors as $errorMessage) {
            $order->addErrorLog($errorMessage->getText());
        }

        foreach ($changeResult->orderItems as $item) {
            $item->setShippingInProgressNo();

            $this->orderItemRepository->save($item);
        }
    }

    private function processSuccess(
        \M2E\TikTokShop\Model\Order $order,
        \M2E\TikTokShop\Model\Order\Change\ShippingProcessor\ChangeResult $changeResult
    ): void {
        $order->addSuccessLog(
            'Tracking number "%num%" for "%code%" has been sent to "%channel_title%".',
            [
                '!num' => $changeResult->trackingNumber,
                '!code' => $changeResult->shippingProviderName,
                '!channel_title' => \M2E\TikTokShop\Helper\Module::getChannelTitle(),
            ]
        );

        if ($changeResult->packageId !== null) {
            foreach ($changeResult->orderItems as $item) {
                $item->setPackageId($changeResult->packageId);
                $item->setItemStatusAsAwaitingCollection();

                $this->orderItemRepository->save($item);
            }
        }

        foreach ($changeResult->messages as $message) {
            $order->addWarningLog($message->getText());
        }
    }
}
