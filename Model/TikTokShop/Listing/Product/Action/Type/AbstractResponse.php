<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type;

abstract class AbstractResponse
{
    private array $params = [];
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData;
    private array $requestMetaData = [];
    private \M2E\TikTokShop\Model\Product $listingProduct;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings;
    private int $statusChanger;

    abstract public function processSuccess(array $response, array $responseParams = []): void;

    public function setStatusChanger(int $statusChanger): void
    {
        $this->statusChanger = $statusChanger;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }

    public function setParams(array $params = []): void
    {
        $this->params = $params;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    // ---------------------------------------

    public function setListingProduct(\M2E\TikTokShop\Model\Product $product): void
    {
        $this->listingProduct = $product;
    }

    protected function getListingProduct(): \M2E\TikTokShop\Model\Product
    {
        return $this->listingProduct;
    }

    // ---------------------------------------

    public function setConfigurator(\M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $object): void
    {
        $this->configurator = $object;
    }

    protected function getConfigurator(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator
    {
        return $this->configurator;
    }

    public function setVariantsSettings(\M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings): void
    {
        $this->variantSettings = $variantSettings;
    }

    protected function getVariantSettings(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings
    {
        return $this->variantSettings;
    }

    // ---------------------------------------

    public function setRequestData(\M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $object): void
    {
        $this->requestData = $object;
    }

    protected function getRequestData(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData
    {
        return $this->requestData;
    }

    // ---------------------------------------

    public function setRequestMetaData(array $value): self
    {
        $this->requestMetaData = $value;

        return $this;
    }

    public function getRequestMetaData(): array
    {
        return $this->requestMetaData;
    }
}
