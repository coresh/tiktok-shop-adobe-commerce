<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ListAction;

class Processor extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\AbstractProcessor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;
    private \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory;
    private ValidatorFactory $actionValidatorFactory;
    private Validator $actionValidator;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ImageResponseHandler $imageResponseHandler;

    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData;
    private array $requestMetadata;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ImageResponseHandler $imageResponseHandler,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->serverClient = $serverClient;
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->imageResponseHandler = $imageResponseHandler;
        $this->productRepository = $productRepository;
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

        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Item\ListCommand(
            $this->getAccount()->getServerHash(),
            $this->requestData->getData(),
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
            $this->requestMetadata
        );

        $responseObj->processSuccess($response->getResponseData());

        $this->processSuccessFoundBrand();

        return $this->createSuccessMessage();
    }

    protected function processFail(\M2E\TikTokShop\Model\Connector\Response $response): void
    {
        if (isset($response->getResponseData()['brand_id'])) {
            $brandId = $response->getResponseData()['brand_id'];
            if ($this->getProduct()->getOnlineBrandId() !== $brandId) {
                $this->getProduct()->setOnlineBrandId($brandId);

                $this->productRepository->save($this->getProduct());
            }
        }

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
            $response
        );
    }

    private function processSuccessFoundBrand(): void
    {
        /** @var \M2E\TikTokShop\Model\Product $listingProduct */
        $listingProduct = $this->getProduct();

        $newBrandId = $listingProduct->getOnlineBrandId();
        $newBrandName = $listingProduct->getOnlineBrandName();

        if (!empty($newBrandId)) {
            $message = sprintf(
                'Brand "%s" was assigned to the Product on TikTok Shop',
                $newBrandName
            );
            $this->addActionLogMessage(\M2E\TikTokShop\Model\Response\Message::createSuccess($message));
        }
    }

    private function createSuccessMessage(): string
    {
        $currencyCode = $this->getProduct()->getListing()->getShop()->getCurrencyCode();
        $currency = $this->localeCurrency->getCurrency($currencyCode);

        $onlineQty = $this->getProduct()->getOnlineQty();

        $minPrice = $this->getProduct()->getMinPrice();
        $maxPrice = $this->getProduct()->getMaxPrice();

        if ($minPrice === $maxPrice) {
            return sprintf(
                'Product was Listed with QTY %d, Price %s',
                $onlineQty,
                $currency->toCurrency($maxPrice),
            );
        }

        return sprintf(
            'Product was Listed with QTY %d, Price %s - %s',
            $onlineQty,
            $currency->toCurrency($minPrice),
            $currency->toCurrency($maxPrice),
        );
    }
}
