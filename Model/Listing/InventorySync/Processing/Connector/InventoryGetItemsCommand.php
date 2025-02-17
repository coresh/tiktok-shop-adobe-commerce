<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Listing\InventorySync\Processing\Connector;

class InventoryGetItemsCommand implements \M2E\Core\Model\Connector\CommandProcessingInterface
{
    private string $accountServerHash;
    private string $shopId;

    public function __construct(string $accountServerHash, string $shopId)
    {
        $this->accountServerHash = $accountServerHash;
        $this->shopId = $shopId;
    }

    public function getCommand(): array
    {
        return ['Inventory', 'Get', 'Items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountServerHash,
            'shop_id' => $this->shopId,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response\Processing {
        return new \M2E\Core\Model\Connector\Response\Processing($response->getResponseData()['processing_id']);
    }
}
