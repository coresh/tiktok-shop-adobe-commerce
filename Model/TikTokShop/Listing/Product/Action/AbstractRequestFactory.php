<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

abstract class AbstractRequestFactory
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
        array $params = []
    ): AbstractRequest {
        /** @var AbstractRequest $obj */
        $obj = $this->objectManager->create($this->getRequestClass());

        $obj->setParams($params);
        $obj->setListingProduct($listingProduct);
        $obj->setConfigurator($configurator);
        $obj->setVariantSettings($variantSettings);
        $obj->setCachedData([]);

        return $obj;
    }

    abstract protected function getRequestClass(): string;
}
