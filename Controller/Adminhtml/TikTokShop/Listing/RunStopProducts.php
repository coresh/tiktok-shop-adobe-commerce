<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

class RunStopProducts extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\AbstractAction
{
    private \M2E\TikTokShop\Controller\Adminhtml\Product\Action\ActionService $actionService;

    public function __construct(
        \M2E\TikTokShop\Controller\Adminhtml\Product\Action\ActionService $actionService,
        \M2E\TikTokShop\Model\Product\Repository $productRepository
    ) {
        parent::__construct($productRepository);

        $this->actionService = $actionService;
    }

    public function execute()
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->setRawContent('You should select Products');
        }

        $products = $this->oldGridLoadProducts($listingsProductsIds);

        if ($this->isRealtimeProcessFromOldGrid()) {
            ['result' => $resultStatus, 'action_id' => $logsActionId] = $this->actionService->runStop($products);
        } else {
            ['result' => $resultStatus, 'action_id' => $logsActionId] = $this->actionService->scheduleStop($products);
        }

        $this->setJsonContent(['result' => $resultStatus, 'action_id' => $logsActionId]);

        return $this->getResult();
    }
}
