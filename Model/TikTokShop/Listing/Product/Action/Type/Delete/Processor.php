<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Delete;

class Processor extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\AbstractProcessor
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;
    private ValidatorFactory $actionValidatorFactory;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;
    private Validator $actionValidator;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\RequestData $requestData;

    public function __construct(
        ValidatorFactory $actionValidatorFactory,
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
        $this->actionValidatorFactory = $actionValidatorFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
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
        foreach ($request->getWarningMessages() as $warningMessage) {
            $this->addActionWarningLog($warningMessage);
        }

        $command = new \M2E\TikTokShop\Model\TikTokShop\Connector\Item\DeleteCommand(
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
            $this->getStatusChanger()
        );

        $responseObj->processSuccess($response->getResponseData());

        return '';
    }

    protected function processFail(\M2E\Core\Model\Connector\Response $response): void
    {
    }
}
