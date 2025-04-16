<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\Realtime;

class ReviseAction extends AbstractRealtime
{
    use \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Manual\SkipMessageTrait;

    protected function getAction(): int
    {
        return \M2E\TikTokShop\Model\Product::ACTION_REVISE;
    }

    protected function calculateAction(\M2E\TikTokShop\Model\Product $product, \M2E\TikTokShop\Model\Product\ActionCalculator $calculator): \M2E\TikTokShop\Model\Product\Action
    {
        $result = $calculator->calculateToReviseOrStop($product, true, true, true, true, true, false);
        if ($result->isActionStop()) {
            return \M2E\TikTokShop\Model\Product\Action::createNothing($product);
        }

        return $result;
    }

    protected function logAboutSkipAction(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\Listing\LogService $logService
    ): void {
        $logService->addProduct(
            $product,
            \M2E\Core\Helper\Data::INITIATOR_USER,
            \M2E\TikTokShop\Model\Listing\Log::ACTION_REVISE_PRODUCT,
            $this->getLogActionId(),
            $this->createSkipReviseMessage(),
            \M2E\TikTokShop\Model\Log\AbstractModel::TYPE_INFO,
        );
    }
}
