<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Realtime;

class RelistAction extends AbstractRealtime
{
    use \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\TikTokShop\Model\Product::ACTION_RELIST;
    }

    protected function calculateAction(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Product\ActionCalculator $calculator
    ): \M2E\TikTokShop\Model\Product\Action {
        return $calculator->calculateToRelist($product, \M2E\TikTokShop\Model\Product::STATUS_CHANGER_USER);
    }

    protected function logAboutSkipAction(
        \M2E\TikTokShop\Model\Product            $product,
        \M2E\TikTokShop\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_RELIST_PRODUCT,
            $this->getLogActionId(),
            $this->createSkipRelistMessage(),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
