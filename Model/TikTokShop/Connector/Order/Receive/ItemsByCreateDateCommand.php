<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive;

class ItemsByCreateDateCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $shopId;
    private \DateTimeInterface $createFrom;
    private \DateTimeInterface $createTo;
    private string $accountHash;

    public function __construct(
        string $accountHash,
        string $shopId,
        \DateTimeInterface $createFrom,
        \DateTimeInterface $createTo
    ) {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->createFrom = $createFrom;
        $this->createTo = $createTo;
    }

    public function getCommand(): array
    {
        return ['orders', 'get', 'items'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
            'from_create_date' => $this->createFrom->format('Y-m-d H:i:s'),
            'to_create_date' => $this->createTo->format('Y-m-d H:i:s'),
        ];
    }

    public function parseResponse(
        \M2E\Core\Model\Connector\Response $response
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Order\Receive\Response {
        $responseData = $response->getResponseData();

        if (!array_key_exists('orders', $responseData)) {
            throw new \M2E\TikTokShop\Model\Exception('Server don`t return "orders" array');
        }

        if (!array_key_exists('to_create_date', $responseData)) {
            throw new \M2E\TikTokShop\Model\Exception('Server don`t return "to_create_date" date');
        }

        if (!array_key_exists('has_more', $responseData)) {
            throw new \M2E\TikTokShop\Model\Exception('Server don`t return "has_more" date');
        }

        return new Response(
            $responseData['orders'],
            $responseData['to_create_date'],
            $responseData['has_more'],
            $response->getMessageCollection()
        );
    }
}
