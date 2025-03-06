<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Channel;

class ResponsiblePersonService
{
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;

    public function __construct(
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient
    ) {
        $this->serverClient = $serverClient;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
     *
     * @return \M2E\TikTokShop\Model\Channel\ResponsiblePerson
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData
     */
    public function create(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
    ): \M2E\TikTokShop\Model\Channel\ResponsiblePerson {
        $command = new \M2E\TikTokShop\Model\Channel\Connector\ResponsiblePerson\AddCommand(
            $account->getServerHash(),
            $responsiblePerson
        );
        /** @var \M2E\TikTokShop\Model\Channel\ResponsiblePerson $response */
        $response = $this->serverClient->process($command);

        return $response;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     *
     * @return \M2E\TikTokShop\Model\Channel\ResponsiblePerson\Collection
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     */
    public function retrieve(
        \M2E\TikTokShop\Model\Account $account
    ): \M2E\TikTokShop\Model\Channel\ResponsiblePerson\Collection {
        $command = new \M2E\TikTokShop\Model\Channel\Connector\ResponsiblePerson\GetCommand(
            $account->getServerHash(),
        );
        /** @var \M2E\TikTokShop\Model\Channel\ResponsiblePerson\Collection $responsiblePersons */
        $responsiblePersons = $this->serverClient->process($command);

        return $responsiblePersons;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
     *
     * @return void
     * @throws \M2E\TikTokShop\Model\Exception
     * @throws \M2E\Core\Model\Exception\Connection
     * @throws \M2E\TikTokShop\Model\Exception\Connection\UnableUpdateData
     */
    public function update(
        \M2E\TikTokShop\Model\Account $account,
        \M2E\TikTokShop\Model\Channel\ResponsiblePerson $responsiblePerson
    ): void {
        $command = new \M2E\TikTokShop\Model\Channel\Connector\ResponsiblePerson\UpdateCommand(
            $account->getServerHash(),
            $responsiblePerson
        );

        $this->serverClient->process($command);
    }
}
