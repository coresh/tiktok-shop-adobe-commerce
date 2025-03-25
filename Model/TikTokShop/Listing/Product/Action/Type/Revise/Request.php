<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise;

class Request extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\AbstractRequest
{
    use \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\RequestTrait;

    public function getActionData(): array
    {
        $listing = $this->getListingProduct()->getListing();

        $request = [
            'item_id' => $this->generateItemId($this->getListingProduct()),
            'shop_id' => $listing->getShop()->getShopId(),
            'product_id' => $this->getListingProduct()->getTTSProductId(),
            'product_data' => [
                'is_cod_allowed' => $this->getListingProduct()
                    ->getSellingFormatTemplateSource()
                    ->isCashOnDeliveryEnabled(),
            ],
        ];

        $request = $this->appendTitle($request);
        $request = $this->appendSkuItems($request);
        $request = $this->appendCategoryData($request);
        $request = $this->appendDescription($request);
        $request = $this->appendImagesData($request);
        $request = $this->appendCertificateData($request);
        $request = $this->appendSizeChartData($request);
        $request = $this->appendBrandData($request);
        $request = $this->appendPackageData($request);
        $request = $this->appendComplianceData($request);
        $request = $this->appendNonSalableFlag($request);

        return $request;
    }

    protected function getAction(): int
    {
        return \M2E\TikTokShop\Model\Product::ACTION_REVISE;
    }
}
