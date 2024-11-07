<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Connector;

interface CommandInterface
{
    public function getCommand(): array;

    public function getRequestData(): array;

    public function parseResponse(\M2E\TikTokShop\Model\Connector\Response $response): object;
}
