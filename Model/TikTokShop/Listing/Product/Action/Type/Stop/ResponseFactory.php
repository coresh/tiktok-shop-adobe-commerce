<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Stop;

class ResponseFactory extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\AbstractResponseFactory
{
    protected function getResponseClass(): string
    {
        return Response::class;
    }
}
