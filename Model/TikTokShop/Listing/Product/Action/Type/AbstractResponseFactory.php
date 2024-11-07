<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type;

abstract class AbstractResponseFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData,
        array $params,
        int $statusChanger,
        array $requestMetadata = []
    ): AbstractResponse {
        /** @var AbstractResponse $obj */
        $obj = $this->objectManager->create($this->getResponseClass());
        $obj->setListingProduct($listingProduct);
        $obj->setConfigurator($configurator);
        $obj->setVariantsSettings($variantSettings);
        $obj->setRequestData($requestData);
        $obj->setParams($params);
        $obj->setStatusChanger($statusChanger);
        $obj->setRequestMetaData($requestMetadata);

        return $obj;
    }

    abstract protected function getResponseClass(): string;
}
