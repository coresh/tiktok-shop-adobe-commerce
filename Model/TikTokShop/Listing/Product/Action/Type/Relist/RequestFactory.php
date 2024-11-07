<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Relist;

class RequestFactory extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\AbstractRequestFactory
{
    protected function getRequestClass(): string
    {
        return Request::class;
    }
}
