<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Item;

class ReviseCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private array $requestData;
    private bool $isPartial;

    public function __construct(string $accountHash, array $requestData, bool $isPartial)
    {
        $this->accountHash = $accountHash;
        $this->requestData = $requestData;
        $this->isPartial = $isPartial;
    }

    public function getCommand(): array
    {
        return ['product', 'update', 'entity'];
    }

    public function getRequestData(): array
    {
        $request = $this->requestData;
        $request['account'] = $this->accountHash;
        $request['is_partial_update'] = $this->isPartial;

        return $request;
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\Core\Model\Connector\Response {
        return $response;
    }
}
