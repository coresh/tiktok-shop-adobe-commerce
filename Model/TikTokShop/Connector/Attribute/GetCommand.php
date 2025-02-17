<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Attribute;

class GetCommand implements \M2E\Core\Model\Connector\CommandInterface
{
    private string $accountHash;
    private string $shopId;
    private string $categoryId;

    public function __construct(string $accountHash, string $shopId, string $categoryId)
    {
        $this->accountHash = $accountHash;
        $this->shopId = $shopId;
        $this->categoryId = $categoryId;
    }

    public function getCommand(): array
    {
        return ['category', 'get', 'attributes'];
    }

    public function getRequestData(): array
    {
        return [
            'shop_id' => $this->shopId,
            'account' => $this->accountHash,
            'category_id' => $this->categoryId,
        ];
    }

    public function parseResponse(\M2E\Core\Model\Connector\Response $response): Get\Response
    {
        $this->processError($response);

        $allowedTypes = [
            Attribute::PRODUCT_TYPE,
            Attribute::SALES_TYPE,
        ];

        $responseData = $response->getResponseData();

        $productCertifications = [];
        foreach ($responseData['rules']['product_certifications'] as $productCertificationData) {
            $productCertifications[] = [
                'id' => $productCertificationData['id'],
                'name' => $productCertificationData['name'],
                'is_required' => $productCertificationData['is_required'],
                'sample_image_url' => $productCertificationData['sample_image_url'],
            ];
        }

        $attributes = [];
        foreach ($responseData['attributes'] as $attributeData) {
            if (!in_array($attributeData['type'], $allowedTypes)) {
                continue;
            }

            $attribute = new Attribute(
                $attributeData['id'],
                $attributeData['name'],
                $attributeData['type'],
                $attributeData['is_required'],
                $attributeData['is_customized'],
                $attributeData['is_multiple_selected']
            );

            foreach ($attributeData['values'] as $value) {
                $attribute->addValue($value['id'], $value['name']);
            }

            $attributes[] = $attribute;
        }

        return new \M2E\TikTokShop\Model\TikTokShop\Connector\Attribute\Get\Response(
            $attributes,
            [
                'product_certifications' => $productCertifications,
                'size_chart' => [
                    'is_supported' => $responseData['rules']['size_chart']['is_supported'],
                    'is_required' => $responseData['rules']['size_chart']['is_required'],
                ],
                'cod' => [
                    'is_supported' => $responseData['rules']['cod']['is_supported'],
                ],
                'package_dimension' => [
                    'is_required' => $responseData['rules']['package_dimension']['is_required'],
                ],
            ]
        );
    }

    private function processError(\M2E\Core\Model\Connector\Response $response): void
    {
        if (!$response->isResultError()) {
            return;
        }

        foreach ($response->getMessageCollection()->getMessages() as $message) {
            if ($message->isError()) {
                throw new \M2E\TikTokShop\Model\Exception\CategoryInvalid(
                    $message->getText(),
                    [],
                    (int)$message->getCode()
                );
            }
        }
    }
}
