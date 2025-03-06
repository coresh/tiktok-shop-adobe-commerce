<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel;

class ManufacturerService
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer
     *
     * @return \M2E\TikTokShop\Model\Channel\Manufacturer
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData
     */
    public function create(\M2E\TikTokShop\Model\Account $account, Manufacturer $manufacturer): Manufacturer
    {
        $command = new \M2E\TikTokShop\Model\Channel\Connector\Manufacturer\AddCommand(
            $account->getServerHash(),
            $manufacturer
        );
        /** @var \M2E\TikTokShop\Model\Channel\Manufacturer $response */
        $response = $this->serverClient->process($command);

        return $response;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     *
     * @return \M2E\TikTokShop\Model\Channel\Manufacturer\Collection
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    public function retrieve(
        \M2E\TikTokShop\Model\Account $account
    ): \M2E\TikTokShop\Model\Channel\Manufacturer\Collection {
        $command = new Connector\Manufacturer\GetCommand(
            $account->getServerHash(),
        );
        /** @var \M2E\TikTokShop\Model\Channel\Manufacturer\Collection $channelManufacturers */
        $channelManufacturers = $this->serverClient->process($command);

        return $channelManufacturers;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\Channel\Manufacturer $manufacturer
     *
     * @return void
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData
     */
    public function update(\M2E\TikTokShop\Model\Account $account, Manufacturer $manufacturer): void
    {
        $command = new \M2E\TikTokShop\Model\Channel\Connector\Manufacturer\UpdateCommand(
            $account->getServerHash(),
            $manufacturer
        );

        /** @var \M2E\TikTokShop\Model\Channel\Manufacturer $response */
        $response = $this->serverClient->process($command);
    }
}
