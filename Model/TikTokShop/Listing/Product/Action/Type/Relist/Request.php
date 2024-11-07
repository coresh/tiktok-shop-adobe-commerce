<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Relist;

class Request extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\Request
{
    public function getActionData(): array
    {
        $this->getConfigurator()->enableAll();

        return parent::getActionData();
    }
}
