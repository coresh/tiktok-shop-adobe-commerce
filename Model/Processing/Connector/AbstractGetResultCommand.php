<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Processing\Connector;

abstract class AbstractGetResultCommand implements \M2E\TikTokShop\Model\Connector\CommandInterface
{
    public function getCommand(): array
    {
        return ['Processing', 'Get', 'Results'];
    }

    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): ResultCollection
    {
        $resultCollection = new ResultCollection();
        foreach ($response->getResponseData()['results'] ?? [] as $hash => $resultData) {
            $resultCollection->add(
                new Result(
                    $hash,
                    $resultData['status'],
                    $resultData['messages'],
                    $resultData['data'],
                    $resultData['next_part']
                )
            );
        }

        return $resultCollection;
    }
}
