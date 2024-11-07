<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector;

interface CommandProcessingInterface extends CommandInterface
{
    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): Response\Processing;
}
