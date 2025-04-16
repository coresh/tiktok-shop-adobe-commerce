<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action;

abstract class AbstractProcessor
{
    /** @var \M2E\Core\Model\Response\Message[] */
    private array $storedActionLogMessages = [];

    private Logger $actionLogger;
    private \M2E\TikTokShop\Model\Product\LockManager $lockManager;
    private \M2E\TikTokShop\Model\Product $product;
    private \M2E\TikTokShop\Model\Account $account;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $actionConfigurator;
    private array $params = [];
    /** @var \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings */
    private VariantSettings $variantSettings;
    private int $statusChanger;

    public function process(): void
    {
        $this->init();

        $this->actionLogger->setStatus(\M2E\Core\Helper\Data::STATUS_SUCCESS);

        if ($this->isListingProductLocked()) {
            $this->actionLogger->logListingProductMessage(
                $this->product,
                \M2E\Core\Model\Response\Message::createError(
                    'Another Action is being processed. Try again when the Action is completed.',
                ),
            );

            return;
        }

        $this->lockManager->lock();

        try {
            if (!$this->validateProduct()) {
                return;
            }

            $apiResponse = $this->makeCall();
            foreach ($apiResponse->getMessageCollection()->getMessages() as $message) {
                $this->addActionLogMessage($message);
            }

            $this->lockManager->unlock();

            if ($apiResponse->isResultError()) {
                $this->processFail($apiResponse);
            } else {
                $successfulMessage = $this->processSuccess($apiResponse);
                if (!empty($successfulMessage)) {
                    $this->addActionLogMessage(
                        \M2E\Core\Model\Response\Message::createSuccess($successfulMessage)
                    );
                }
            }

            $this->processComplete($apiResponse);
        } finally {
            $this->flushActionLogs();
            $this->lockManager->unlock();
        }
    }

    public function getResultStatus(): int
    {
        return $this->actionLogger->getStatus();
    }

    private function isListingProductLocked(): bool
    {
        return $this->lockManager->isLocked();
    }

    private function validateProduct(): bool
    {
        $productActionValidator = $this->getActionValidator();

        $validationResult = $productActionValidator->validate(
            $this->getProduct(),
            $this->getActionConfigurator(),
            $this->getVariantSettings(),
        );

        foreach ($productActionValidator->getMessages() as $message) {
            $this->addActionLogMessage($message);
        }

        return $validationResult;
    }

    abstract protected function getActionValidator(): Type\ValidatorInterface;

    // ----------------------------------------

    abstract protected function makeCall(): \M2E\Core\Model\Connector\Response;

    /**
     * @param \M2E\Core\Model\Connector\Response $response
     *
     * @return string - successful message
     */
    abstract protected function processSuccess(\M2E\Core\Model\Connector\Response $response): string;

    abstract protected function processFail(\M2E\Core\Model\Connector\Response $response): void;

    protected function processComplete(\M2E\Core\Model\Connector\Response $response): void
    {
    }

    // init
    // ----------------------------------------

    private function init(): void
    {
        $this->storedActionLogMessages = [];
        if (
            !isset(
                $this->actionLogger,
                $this->lockManager,
                $this->product,
                $this->account,
                $this->actionConfigurator,
                $this->variantSettings,
            )
        ) {
            throw new \LogicException('Processor was not initialized.');
        }
    }

    public function setStatusChanger(int $statusChanger): void
    {
        $this->statusChanger = $statusChanger;
    }

    protected function getStatusChanger(): int
    {
        return $this->statusChanger;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    protected function getParams(): array
    {
        return $this->params;
    }

    public function setActionLogger(\M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Logger $logger): void
    {
        $this->actionLogger = $logger;
    }

    public function setLockManager(\M2E\TikTokShop\Model\Product\LockManager $lockManager): void
    {
        $this->lockManager = $lockManager;
    }

    public function setProduct(\M2E\TikTokShop\Model\Product $product): void
    {
        $this->product = $product;
        $this->account = $this->product->getAccount();
    }

    protected function getProduct(): \M2E\TikTokShop\Model\Product
    {
        return $this->product;
    }

    protected function getAccount(): \M2E\TikTokShop\Model\Account
    {
        return $this->account;
    }

    public function setActionConfigurator(
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $configurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): void {
        $this->actionConfigurator = $configurator;
        $this->variantSettings = $variantSettings;
    }

    protected function getActionConfigurator(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator
    {
        return $this->actionConfigurator;
    }

    protected function getVariantSettings(): \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings
    {
        return $this->variantSettings;
    }

    // ----------------------------------------

    protected function addActionErrorLog(string $message): void
    {
        $this->addActionLogMessage(\M2E\Core\Model\Response\Message::createError($message));
    }

    protected function addActionWarningLog(string $message): void
    {
        $this->addActionLogMessage(\M2E\Core\Model\Response\Message::createWarning($message));
    }

    protected function addActionNoticeLog(string $message): void
    {
        $this->addActionLogMessage(\M2E\Core\Model\Response\Message::createNotice($message));
    }

    protected function addActionSuccessLog(string $message): void
    {
        $this->addActionLogMessage(\M2E\Core\Model\Response\Message::createSuccess($message));
    }

    protected function addActionLogMessage(\M2E\Core\Model\Response\Message $message): void
    {
        $this->storedActionLogMessages[] = $message;
    }

    private function flushActionLogs(): void
    {
        foreach ($this->storedActionLogMessages as $message) {
            $this->actionLogger->logListingProductMessage(
                $this->product,
                $message,
            );
        }

        $this->storedActionLogMessages = [];
    }
}
