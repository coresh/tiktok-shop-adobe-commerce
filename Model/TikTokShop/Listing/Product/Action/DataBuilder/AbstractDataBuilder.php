<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\DataBuilder;

abstract class AbstractDataBuilder
{
    private \M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper;

    private \M2E\TikTokShop\Model\Product $listingProduct;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator;
    private array $cachedData = [];
    private array $params = [];
    private array $warningMessages = [];
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings;
    private int $action;

    public function __construct(\M2E\TikTokShop\Helper\Magento\Attribute $magentoAttributeHelper)
    {
        $this->magentoAttributeHelper = $magentoAttributeHelper;
    }

    public function init(
        \M2E\TikTokShop\Model\Product $listingProduct,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings,
        int $action,
        array $params,
        array $cacheData = []
    ): void {
        $this->listingProduct = $listingProduct;
        $this->configurator = $configurator;
        $this->variantSettings = $variantSettings;
        $this->params = $params;
        $this->cachedData = $cacheData;
        $this->action = $action;
    }

    // ----------------------------------------

    abstract public function getBuilderData(): array;

    public function getMetaData(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }

    // ----------------------------------------

    protected function getParams(): array
    {
        return $this->params;
    }

    protected function getCacheData(): array
    {
        return $this->cachedData;
    }

    // ----------------------------------------

    protected function getAction(): int
    {
        return $this->action;
    }

    protected function getConfigurator(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator
    {
        return $this->configurator;
    }

    protected function getVariantSettings(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings
    {
        return $this->variantSettings;
    }

    protected function getListingProduct(): \M2E\TikTokShop\Model\Product
    {
        return $this->listingProduct;
    }

    protected function getShop(): \M2E\TikTokShop\Model\Shop
    {
        return $this->getListing()->getShop();
    }

    protected function getAccount(): \M2E\TikTokShop\Model\Account
    {
        return $this->getListing()->getAccount();
    }

    protected function getListing(): \M2E\TikTokShop\Model\Listing
    {
        return $this->getListingProduct()->getListing();
    }

    // ---------------------------------------

    protected function searchNotFoundAttributes(\M2E\TikTokShop\Model\Magento\Product $magentoProduct): void
    {
        $magentoProduct->clearNotFoundAttributes();
    }

    protected function processNotFoundAttributes(
        string $title,
        \M2E\TikTokShop\Model\Magento\Product $magentoProduct
    ): bool {
        $attributes = $magentoProduct->getNotFoundAttributes();
        if (empty($attributes)) {
            return true;
        }

        $this->addNotFoundAttributesMessages($title, $attributes);

        return false;
    }

    // ---------------------------------------

    protected function addWarningMessage(string $message): AbstractDataBuilder
    {
        $this->warningMessages[sha1($message)] = $message;

        return $this;
    }

    private function addNotFoundAttributesMessages(string $title, array $attributes): void
    {
        $attributesTitles = [];

        foreach ($attributes as $attribute) {
            $attributesTitles[] = $this->magentoAttributeHelper
                ->getAttributeLabel(
                    $attribute,
                    $this->getListing()->getStoreId(),
                );
        }

        $this->addWarningMessage(
            (string)__(
                '%1: Attribute(s) %2 were not found' .
                ' in this Product and its value was not sent.',
                $title,
                implode(', ', $attributesTitles),
            ),
        );
    }

    // ---------------------------------------

    protected function isReviseAction(): bool
    {
        return $this->getAction() === \M2E\TikTokShop\Model\Product::ACTION_REVISE;
    }

    protected function isListingAction(): bool
    {
        return $this->getAction() === \M2E\TikTokShop\Model\Product::ACTION_LIST;
    }
}
