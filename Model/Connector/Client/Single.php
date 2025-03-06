<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector\Client;

class Single
{
    private \M2E\Core\Model\Connector\Client\Single $client;

    private \M2E\Core\Model\Connector\Client\SingleFactory $coreClientFactory;
    private \M2E\TikTokShop\Model\Connector\Client\Config $config;
    private \M2E\TikTokShop\Model\Connector\Client\ModuleInfo $moduleInfo;
    private \M2E\TikTokShop\Model\Connector\Protocol $protocol;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionLogger;

    public function __construct(
        \M2E\Core\Model\Connector\Client\SingleFactory $coreClientFactory,
        \M2E\TikTokShop\Model\Connector\Client\Config $config,
        \M2E\TikTokShop\Model\Connector\Client\ModuleInfo $moduleInfo,
        \M2E\TikTokShop\Model\Connector\Protocol $protocol,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionLogger
    ) {
        $this->coreClientFactory = $coreClientFactory;
        $this->config = $config;
        $this->moduleInfo = $moduleInfo;
        $this->protocol = $protocol;
        $this->exceptionLogger = $exceptionLogger;
    }

    /**
     * @param \M2E\Core\Model\Connector\CommandInterface $command
     *
     * @return object
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\Core\Model\Exception\Connection\SystemError
     */
    public function process(\M2E\Core\Model\Connector\CommandInterface $command): object
    {
        try {
            $commandResponseResult = $this->getClient()->process($command);
        } catch (\Throwable $exception) {
            $this->exceptionLogger->process($exception, ['command' => $command->getCommand()]);

            throw $exception;
        }

        return $commandResponseResult;
    }

    private function getClient(): \M2E\Core\Model\Connector\Client\Single
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->client)) {
            $this->client = $this->coreClientFactory->create(
                $this->protocol,
                $this->config,
                $this->moduleInfo
            );
        }

        return $this->client;
    }
}
