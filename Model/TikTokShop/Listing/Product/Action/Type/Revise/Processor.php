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
    private LoggerFactory $loggerFactory;

    public function __construct(
        LoggerFactory $loggerFactory,
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\TikTokShop\Model\Tag\ListingProduct\Buffer $tagBuffer,
        \M2E\TikTokShop\Model\TikTokShop\TagFactory $tagFactory,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ImageResponseHandler $imageResponseHandler,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\TagManager $tagManager
    ) {
        parent::__construct($tagManager);

        $this->loggerFactory = $loggerFactory;
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

        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Item\ReviseCommand(
            $this->getAccount()->getServerHash(),
            $this->requestData->getData(),
            true,
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

        $logger = $this->loggerFactory->create();
        $logger->saveProductDataBeforeUpdate($this->getProduct());

        $responseObj->processSuccess($response->getResponseData());

        $logs = $logger->calculateLogs($this->getProduct(), $this->requestMetadata);

        if (empty($logs)) {
            return 'Item was Revised';
        }

        foreach ($logs as $log) {
            $this->addActionLogMessage($log);
        }

        return '';
    }

    protected function processFail(\M2E\Core\Model\Connector\Response $response): void
    {
        $this->addTags($response->getMessageCollection()->getMessages());
    }

    /**
     * @param \M2E\Core\Model\Connector\Response\Message[] $messages
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
}
