<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

class Logger
{
    private int $action;
    private int $actionId;
    private int $initiator;

    private int $status = \M2E\TikTokShop\Helper\Data::STATUS_SUCCESS;

    private \M2E\TikTokShop\Model\Listing\LogService $listingLogService;

    public function __construct(
        int $actionId,
        int $action,
        int $initiator,
        \M2E\TikTokShop\Model\Listing\LogService $listingLogService
    ) {
        $this->actionId = $actionId;
        $this->action = $action;
        $this->initiator = $initiator;
        $this->listingLogService = $listingLogService;
    }

    public function getActionId(): int
    {
        return $this->actionId;
    }

    public function getAction(): int
    {
        return $this->action;
    }

    public function getInitiator(): int
    {
        return $this->initiator;
    }

    // ---------------------------------------

    public function setStatus(int $status): void
    {
        if ($status === \M2E\TikTokShop\Helper\Data::STATUS_ERROR) {
            $this->status = \M2E\TikTokShop\Helper\Data::STATUS_ERROR;

            return;
        }

        if ($this->status === \M2E\TikTokShop\Helper\Data::STATUS_ERROR) {
            return;
        }

        if ($status === \M2E\TikTokShop\Helper\Data::STATUS_WARNING) {
            $this->status = \M2E\TikTokShop\Helper\Data::STATUS_WARNING;

            return;
        }

        if ($this->status === \M2E\TikTokShop\Helper\Data::STATUS_WARNING) {
            return;
        }

        $this->status = \M2E\TikTokShop\Helper\Data::STATUS_SUCCESS;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    // ----------------------------------------

    public function logListingProductMessage(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\Response\Message $message
    ): void {
        $this->listingLogService->addProduct(
            $listingProduct,
            $this->initiator,
            $this->action,
            $this->actionId,
            $message->getText(),
            $this->initLogType($message)
        );
    }

    protected function initLogType(\M2E\TikTokShop\Model\Response\Message $message): int
    {
        if ($message->isError()) {
            $this->setStatus(\M2E\TikTokShop\Helper\Data::STATUS_ERROR);

            return \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR;
        }

        if ($message->isWarning()) {
            $this->setStatus(\M2E\TikTokShop\Helper\Data::STATUS_WARNING);

            return \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_WARNING;
        }

        if ($message->isSuccess()) {
            $this->setStatus(\M2E\TikTokShop\Helper\Data::STATUS_SUCCESS);

            return \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_SUCCESS;
        }

        if ($message->isNotice()) {
            $this->setStatus(\M2E\TikTokShop\Helper\Data::STATUS_SUCCESS);

            return \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO;
        }

        $this->setStatus(\M2E\TikTokShop\Helper\Data::STATUS_ERROR);

        return \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_ERROR;
    }
}
