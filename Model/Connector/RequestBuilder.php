<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector;

class RequestBuilder
{
    private const API_VERSION = 1;

    private \M2E\TikTokShop\Helper\Magento $magentoHelper;
    private \M2E\TikTokShop\Model\Module $module;
    private \M2E\TikTokShop\Helper\Client $clientHelper;
    private \M2E\TikTokShop\Model\Connector\Client\Config $config;

    public function __construct(
        \M2E\TikTokShop\Model\Connector\Client\Config $config,
        \M2E\TikTokShop\Helper\Magento $magentoHelper,
        \M2E\TikTokShop\Model\Module $module,
        \M2E\TikTokShop\Helper\Client $clientHelper
    ) {
        $this->magentoHelper = $magentoHelper;
        $this->module = $module;
        $this->clientHelper = $clientHelper;
        $this->config = $config;
    }

    public function build(
        \M2E\TikTokShop\Model\Connector\CommandInterface $command,
        \M2E\TikTokShop\Model\Connector\ProtocolInterface $protocol
    ): array {
        $request = new \M2E\TikTokShop\Model\Connector\Request();

        $request->setComponent($protocol->getComponent())
                ->setComponentVersion($protocol->getComponentVersion())
                ->setCommand($command->getCommand())
                ->setInput($command->getRequestData())
                ->setPlatform(
                    sprintf('%s (%s)', $this->magentoHelper->getName(), $this->magentoHelper->getEditionName()),
                    $this->magentoHelper->getVersion(false),
                )
                ->setModule($this->module->getName(), $this->module->getPublicVersion())
                ->setLocation($this->clientHelper->getDomain(), $this->clientHelper->getIp())
                ->setAuth(
                    $this->config->getApplicationKey(),
                    $this->config->getLicenseKey(),
                );

        return [
            'api_version' => self::API_VERSION,
            'request' => \M2E\TikTokShop\Helper\Json::encode($request->getInfo()),
            'data' => \M2E\TikTokShop\Helper\Json::encode($request->getInput()),
        ];
    }
}
