<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\DeclineReasons;

use M2E\TikTokShop\Model\Channel\Order\ReturnRequest\DeclineReason;

class Command implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;
    private string $orderId;
    private string $refundReturnId;

    public function __construct(
        string $accountHash,
        string $shopId,
        string $orderId,
        string $refundReturnId
    ) {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->orderId = $orderId;
        $this->refundReturnId = $refundReturnId;
    }

    public function getCommand(): array
    {
        return ['Order', 'RefundReturn', 'DeclineReasons'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
            'order_id' => $this->orderId,
            'refund_return_id' => $this->refundReturnId
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): object
    {
        $data = $response->getResponseData();
        $reasons = [];

        if (isset($data['reasons'])) {
            foreach ($data['reasons'] as $reason) {
                $reasons[] = new DeclineReason(
                    $reason['name'],
                    $reason['text']
                );
            }
        }

        return new \M2E\TikTokShop\Model\Channel\Connector\Order\ReturnRequest\DeclineReasons\Command\Response(
            $reasons,
            $response->getMessageCollection()
        );
    }
}
