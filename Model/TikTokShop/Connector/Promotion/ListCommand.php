<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Promotion;

class ListCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;

    public function __construct(string $accountHash, string $shopId)
    {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
    }

    public function getCommand(): array
    {
        return ['promotion', 'get', 'list'];
    }

    public function getRequestData(): array
    {
        return [
            'account' => $this->accountHash,
            'shop_id' => $this->shopId,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $responseData = $response->getResponseData();

        $promotions = [];
        $responseDataPromotions = $responseData['promotions'] ?? [];
        foreach ($responseDataPromotions as $promotion) {
            $promotionProducts = [];

            foreach ($promotion['products'] as $promotionProduct) {
                if (isset($promotionProduct['skus'])) {
                    $promotionProductSkus = [];
                    foreach ($promotionProduct['skus'] as $promotionProductSku) {
                        $promotionProductSkus[] = new \M2E\TikTokShop\Model\Promotion\Channel\Sku(
                            $promotionProductSku['id'] ?? null,
                            isset($promotionProductSku['price']['amount']) ?
                                (float)$promotionProductSku['price']['amount'] : null,
                            $promotionProductSku['discount'] ?? null,
                            $promotionProductSku['quantity_limit'] ?? null,
                            $promotionProductSku['quantity_per_user'] ?? null,
                        );
                    }
                }

                $promotionProducts[] = new \M2E\TikTokShop\Model\Promotion\Channel\Product(
                    $promotionProduct['id'],
                    isset($promotionProduct['price']['amount']) ? (float)$promotionProduct['price']['amount'] : null,
                    $promotionProduct['discount'] ?? null,
                    $promotionProduct['quantity_limit'] ?? null,
                    $promotionProduct['quantity_per_user'] ?? null,
                    $promotionProductSkus ?? []
                );
            }

            $promotions[] = new \M2E\TikTokShop\Model\Promotion\Channel\Promotion(
                $promotion['id'],
                $promotion['title'],
                $promotion['type'],
                $promotion['status'],
                $promotion['product_level'],
                \M2E\TikTokShop\Helper\Date::createDateGmt($promotion['start_date']),
                \M2E\TikTokShop\Helper\Date::createDateGmt($promotion['end_date']),
                $promotionProducts
            );
        }

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Promotion\Get\Response($promotions);
    }
}
