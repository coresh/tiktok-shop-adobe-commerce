<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

abstract class AbstractRequest
{
    protected array $cachedData = [];
    private array $params = [];
    private Configurator $configurator;
    private array $warningMessages = [];
    protected array $metaData = [];
    private \M2E\TikTokShop\Model\Product $listingProduct;
    private RequestData $requestData;

    // ----------------------------------------
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings */
    private VariantSettings $variantSettings;

    public function setCachedData(array $data): void
    {
        $this->cachedData = $data;
    }

    /**
     * @return array
     */
    public function getCachedData(): array
    {
        return $this->cachedData;
    }

    // ----------------------------------------

    public function setParams(array $params = []): void
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    protected function getParams(): array
    {
        return $this->params;
    }

    // ---------------------------------------

    public function setConfigurator(Configurator $object): void
    {
        $this->configurator = $object;
    }

    protected function getConfigurator(): Configurator
    {
        return $this->configurator;
    }

    public function setVariantSettings(\M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings): void
    {
        $this->variantSettings = $variantSettings;
    }

    protected function getVariantSettings(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings
    {
        return $this->variantSettings;
    }

    // ----------------------------------------

    protected function addWarningMessage($message): void
    {
        $this->warningMessages[sha1($message)] = $message;
    }

    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }

    // ----------------------------------------

    public function getMetaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData($value): self
    {
        $this->metaData = $value;

        return $this;
    }

    public function setListingProduct(\M2E\TikTokShop\Model\Product $object): void
    {
        $this->listingProduct = $object;
    }

    protected function getListingProduct(): \M2E\TikTokShop\Model\Product
    {
        return $this->listingProduct;
    }

    public function build(): RequestData
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->requestData)) {
            return $this->requestData;
        }

        $data = $this->getRequestData();

        $requestData = new RequestData($this->getListingProduct());
        $requestData->setData($data);

        return $this->requestData = $requestData;
    }

    abstract public function getRequestData(): array;

    // ---------------------------------------

    protected function getAccount(): \M2E\TikTokShop\Model\Account
    {
        return $this->getListing()->getAccount();
    }

    // ---------------------------------------

    protected function getMagentoProduct(): \M2E\TikTokShop\Model\Magento\Product\Cache
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ---------------------------------------

    protected function getListing(): \M2E\TikTokShop\Model\Listing
    {
        return $this->getListingProduct()->getListing();
    }
}
