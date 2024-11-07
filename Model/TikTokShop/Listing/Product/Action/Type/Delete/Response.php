<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Delete;

class Response extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\TikTokShop\Model\Product\RemoveHandler $removeHandlerFactory;
    private \M2E\TikTokShop\Helper\Data $dataHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Product\RemoveHandler $removeHandler,
        \M2E\TikTokShop\Helper\Data $dataHelper
    ) {
        $this->removeHandlerFactory = $removeHandler;
        $this->dataHelper = $dataHelper;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        $this->removeHandlerFactory->process(
            $this->getListingProduct(),
            $this->dataHelper->findInitiatorByProductStatusChanger($this->getStatusChanger())
        );
    }
}
