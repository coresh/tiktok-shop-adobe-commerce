<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Realtime;

class ListAction extends AbstractRealtime
{
    use \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\TikTokShop\Model\Product::ACTION_LIST;
    }

    protected function calculateAction(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Product\ActionCalculator $calculator
    ): \M2E\TikTokShop\Model\Product\Action {
        return $calculator->calculateToList($product);
    }

    protected function logAboutSkipAction(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\TikTokShop\Helper\Data::INITIATOR_USER,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_LIST_PRODUCT,
            null,
            $this->createSkipListMessage(),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
