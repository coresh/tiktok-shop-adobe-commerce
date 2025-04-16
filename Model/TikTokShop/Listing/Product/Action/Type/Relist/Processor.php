<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Relist;

class Processor extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\AbstractProcessor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;
    private \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer;
    private \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory;
    private ValidatorFactory $actionValidatorFactory;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private Validator $actionValidator;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ImageResponseHandler $imageResponseHandler;
    private array $requestMetadata;
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\InstructionService $instructionService;
    private \M2E\TikTokShop\Model\Product\CalculateStatusByChannel $calculateStatusByChannel;

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ImageResponseHandler $imageResponseHandler,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\InstructionService $instructionService,
        \M2E\TikTokShop\Model\Product\CalculateStatusByChannel $calculateStatusByChannel
    ) {
        $this->serverClient = $serverClient;
        $this->tagBuffer = $tagBuffer;
        $this->tagFactory = $tagFactory;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->imageResponseHandler = $imageResponseHandler;
        $this->localeCurrency = $localeCurrency;
        $this->productRepository = $productRepository;
        $this->instructionService = $instructionService;
        $this->calculateStatusByChannel = $calculateStatusByChannel;
    }

    protected function getActionValidator(): Validator
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->actionValidator)) {
            return $this->actionValidator;
        }

        return $this->actionValidator = $this->actionValidatorFactory->create();
    }

    protected function makeCall(): \M2E\Core\Model\Connector\Response
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

        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Item\RelistCommand(
            $this->getAccount()->getServerHash(),
            $this->requestData->getData(),
        );

        /** @var \M2E\Core\Model\Connector\Response */
        return $this->serverClient->process($command);
    }

    protected function processSuccess(\M2E\Core\Model\Connector\Response $response): string
    {
        /** @var Response $responseObj */
        $responseObj = $this->responseFactory->create(
            $this->getProduct(),
            $this->getVariantSettings(),
            $this->requestData,
            $this->getParams(),
            $this->getStatusChanger(),
            $this->requestMetadata,
        );

        $responseObj->processSuccess($response->getResponseData());

        // ----------------------------------------

        return $this->createSuccessMessage();
    }

    protected function processFail(\M2E\Core\Model\Connector\Response $response): void
    {
        $actualChannelStatus = $response->getResponseData()['current_status'] ?? null;
        if ($actualChannelStatus !== null) {
            $this->calculateRightProductsStatus($this->getProduct(), $actualChannelStatus);
        }

        $this->addTags($response->getMessageCollection()->getMessages());
    }

    private function calculateRightProductsStatus(
        \M2E\TikTokShop\Model\Product $product,
        string $actualChannelStatus
    ): void {
        $calculateStatusResult = $this->calculateStatusByChannel->calculate($product, $actualChannelStatus);
        if ($calculateStatusResult === null) {
            return;
        }

        switch ($calculateStatusResult->getStatus()) {
            case \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED:
                $product->setStatusNotListed(\M2E\TikTokShop\Model\Product::STATUS_CHANGER_COMPONENT);
                break;

            case \M2E\TikTokShop\Model\Product::STATUS_BLOCKED:
                $product->setStatusBlocked(\M2E\TikTokShop\Model\Product::STATUS_CHANGER_COMPONENT);
                break;

            default:
                $product->setStatus(
                    $calculateStatusResult->getStatus(),
                    \M2E\TikTokShop\Model\Product::STATUS_CHANGER_COMPONENT,
                );
        }

        $this->productRepository->save($product);

        $this->addActionNoticeLog(
            $calculateStatusResult->getMessageAboutChange()->getMessage()
        );

        $this->instructionService->createBatch([
            $calculateStatusResult->getInstructionData(),
        ]);
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

    protected function processComplete(\M2E\Core\Model\Connector\Response $response): void
    {
        $this->imageResponseHandler->handleResponse(
            $this->getProduct()->getId(),
            $this->requestData,
            $response,
        );
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
                'Product was Relisted with QTY %d, Price %s',
                $onlineQty,
                $currency->toCurrency($maxPrice),
            );
        }

        return sprintf(
            'Product was Relisted with QTY %d, Price %s - %s',
            $onlineQty,
            $currency->toCurrency($minPrice),
            $currency->toCurrency($maxPrice),
        );
    }
}
