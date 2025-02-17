<?php

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Item;

class GetInfoCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $ttsProductId;
    private string $accountHash;

    public function __construct(string $ttsProductId, string $accountHash)
    {
        $this->ttsProductId = $ttsProductId;
        $this->accountHash = $accountHash;
    }

    public function getCommand(): array
    {
        return ['inventory', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'product_id' => $this->ttsProductId,
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response {
        return $response;
    }
}
