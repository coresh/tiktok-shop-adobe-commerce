<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise;

class Processor extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\AbstractProcessor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;
    private ValidatorFactory $actionValidatorFactory;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory;
    private Validator $actionValidator;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ImageResponseHandler $imageResponseHandler;
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    private array $requestMetadata;

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ImageResponseHandler $imageResponseHandler,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->serverClient = $serverClient;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->imageResponseHandler = $imageResponseHandler;
        $this->localeCurrency = $localeCurrency;
    }

    protected function getActionValidator(): Validator
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->actionValidator)) {
            return $this->actionValidator;
        }

        return $this->actionValidator = $this->actionValidatorFactory->create();
    }

    protected function makeCall(): \M2E\TikTokShop\Model\Connector\Response
    {
        $request = $this->requestFactory->create(
            $this->getProduct(),
            $this->getActionConfigurator(),
            $this->getVariantSettings(),
            $this->getParams(),
        );

        $this->requestData = $request->build();
        $this->requestMetadata = $request->getMetaData();
        foreach ($request->getWarningMessages() as $warningMessage) {
            $this->addActionWarningLog($warningMessage);
        }

        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Item\ReviseCommand(
            $this->getAccount()->getServerHash(),
            $this->requestData->getData(),
            true,
        );

        /** @var \M2E\TikTokShop\Model\Connector\Response */
        return $this->serverClient->process($command);
    }

    protected function processSuccess(\M2E\TikTokShop\Model\Connector\Response $response): string
    {
        /** @var Response $responseObj */
        $responseObj = $this->responseFactory->create(
            $this->getProduct(),
            $this->getActionConfigurator(),
            $this->getVariantSettings(),
            $this->requestData,
            $this->getParams(),
            $this->getStatusChanger(),
            $this->requestMetadata,
        );

        $variantOnlineDataBefore = $this->getProduct()->getVariantOnlineData();

        $brandIdBefore = $this->getProduct()->getOnlineBrandId();
        $brandNameBefore = $this->getProduct()->getOnlineBrandName();

        $responseObj->processSuccess($response->getResponseData());

        $this->processSuccessRevisePrice($variantOnlineDataBefore);
        $this->processSuccessReviseQty($variantOnlineDataBefore);
        $this->processSuccessFoundBrand($brandIdBefore, $brandNameBefore);

        $sequenceStrings = [];
        $isPlural = false;

        if ($this->getActionConfigurator()->isTitleAllowed()) {
            $sequenceStrings[] = 'Title';
        }

        if ($this->getActionConfigurator()->isDescriptionAllowed()) {
            $sequenceStrings[] = 'Description';
        }

        if ($this->getActionConfigurator()->isImagesAllowed()) {
            $sequenceStrings[] = 'Images';
            $isPlural = true;
        }

        if ($this->getActionConfigurator()->isCategoriesAllowed()) {
            $sequenceStrings[] = 'Categories';
            $isPlural = true;
        }

        if (empty($sequenceStrings)) {
            return 'Item was Revised';
        }

        if (count($sequenceStrings) === 1) {
            $verb = $isPlural ? 'were' : 'was';

            return $sequenceStrings[0] . ' ' . $verb . ' Revised';
        }

        return implode(', ', $sequenceStrings) . ' were Revised';
    }

    protected function processFail(\M2E\TikTokShop\Model\Connector\Response $response): void
    {
        $this->addTags($response->getMessageCollection()->getMessages());
    }

    /**
     * @param \M2E\TikTokShop\Model\Connector\Response\Message[] $messages
     *
     * @return void
     */
    private function addTags(
        array $messages
    ): void {
        $allowedCodesOfWarnings = [];

        $tags = [];
        foreach ($messages as $message) {
            if (
                !$message->isSenderComponent()
                || empty($message->getCode())
            ) {
                continue;
            }

            if (
                $message->isError()
                || ($message->isWarning() && in_array($message->getCode(), $allowedCodesOfWarnings))
            ) {
                $tags[] = $this->tagFactory->createByErrorCode((string)$message->getCode(), $message->getText());
            }
        }

        if (!empty($tags)) {
            $this->tagBuffer->addTags($this->getProduct(), $tags);
            $this->tagBuffer->flush();
        }
    }

    protected function processComplete(\M2E\TikTokShop\Model\Connector\Response $response): void
    {
        $this->imageResponseHandler->handleResponse(
            $this->getProduct()->getId(),
            $this->requestData,
            $response,
        );
    }

    // ----------------------------------------

    /**
     * @param \M2E\TikTokShop\Model\Product\VariantSku\OnlineData[] $beforeVariantOnlineData
     *
     * @return void
     * @throws \Magento\Framework\Currency\Exception\CurrencyException
     */
    private function processSuccessRevisePrice(array $beforeVariantOnlineData): void
    {
        if (!$this->getActionConfigurator()->isVariantsAllowed()) {
            return;
        }

        $beforeData = [];
        foreach ($beforeVariantOnlineData as $onlineData) {
            $beforeData[$onlineData->getVariantId()] = $onlineData;
        }

        $currencyCode = $this->getProduct()->getListing()->getShop()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);
        foreach ($this->getProduct()->getVariants() as $variant) {
            $newOnlineData = $variant->getOnlineData();
            $from = 'N/A';
            if (isset($beforeData[$newOnlineData->getVariantId()])) {
                $from = $beforeData[$newOnlineData->getVariantId()]->getPrice();
            }

            if ($from === $newOnlineData->getPrice()) {
                continue;
            }

            if ($this->getProduct()->isSimple()) {
                $message = sprintf(
                    'Price was revised from %s to %s',
                    $currency->toCurrency($from),
                    $currency->toCurrency($newOnlineData->getPrice()),
                );
            } else {
                $message = sprintf(
                    'SKU %s: Price was revised from %s to %s',
                    $newOnlineData->getSku(),
                    $currency->toCurrency($from),
                    $currency->toCurrency($newOnlineData->getPrice()),
                );
            }

            $this->addActionLogMessage(\M2E\TikTokShop\Model\Response\Message::createSuccess($message));
        }
    }

    /**
     * @param \M2E\TikTokShop\Model\Product\VariantSku\OnlineData[] $beforeVariantOnlineData
     *
     * @return void
     */
    private function processSuccessReviseQty(array $beforeVariantOnlineData): void
    {
        if (!$this->getActionConfigurator()->isVariantsAllowed()) {
            return;
        }

        $beforeData = [];
        foreach ($beforeVariantOnlineData as $onlineData) {
            $beforeData[$onlineData->getVariantId()] = $onlineData;
        }

        foreach ($this->getProduct()->getVariants() as $variant) {
            $newOnlineData = $variant->getOnlineData();
            $from = 'N/A';
            if (isset($beforeData[$newOnlineData->getVariantId()])) {
                $from = $beforeData[$newOnlineData->getVariantId()]->getQty();
            }

            if ($from === $newOnlineData->getQty()) {
                continue;
            }

            if ($this->getProduct()->isSimple()) {
                $message = sprintf(
                    'QTY was revised from %s to %s',
                    $from,
                    $newOnlineData->getQty()
                );
            } else {
                $message = sprintf(
                    'SKU %s: QTY was revised from %s to %s',
                    $newOnlineData->getSku(),
                    $from,
                    $newOnlineData->getQty()
                );
            }

            $this->addActionLogMessage(\M2E\TikTokShop\Model\Response\Message::createSuccess($message));
        }
    }

    private function processSuccessFoundBrand(string $beforeBrandId, string $beforeBrandName): void
    {
        $listingProduct = $this->getProduct();

        $newBrandId = $listingProduct->getOnlineBrandId();
        $newBrandName = $listingProduct->getOnlineBrandName();

        if (empty($beforeBrandId) && !empty($newBrandId)) {
            $message = sprintf(
                'Brand "%s" was assigned to the Product on TikTok Shop',
                $newBrandName
            );
            $this->addActionLogMessage(\M2E\TikTokShop\Model\Response\Message::createSuccess($message));
        }

        if (!empty($beforeBrandId) && !empty($newBrandId) && $beforeBrandId !== $newBrandId) {
            $message = sprintf('Brand was changed from "%s" to "%s"', $beforeBrandName, $newBrandName);
            $this->addActionLogMessage(\M2E\TikTokShop\Model\Response\Message::createSuccess($message));
        }
    }
}
