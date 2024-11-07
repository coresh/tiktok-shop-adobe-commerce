<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing;

class RunListProducts extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\AbstractAction
{
    private \M2E\TikTokShop\Controller\Adminhtml\Product\Action\ActionService $actionService;
    private \M2E\TikTokShop\Model\Shop\CheckStatus $checkShopStatus;
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\TikTokShop\Controller\Adminhtml\Product\Action\ActionService $actionService,
        \M2E\TikTokShop\Model\Product\Repository $productRepository,
        \M2E\TikTokShop\Model\Shop\CheckStatus $checkShopStatus,
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct($productRepository);

        $this->actionService = $actionService;
        $this->checkShopStatus = $checkShopStatus;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return $this->setRawContent('You should select Products');
        }

        $products = $this->oldGridLoadProducts($listingsProductsIds);
        $id = (int)$this->getRequest()->getParam('id');

        if (count($issues = $this->checkShopStatus->getIssues($this->listingRepository->get($id)))) {
            $issues = $this->getUniqueIssues($issues);

            $messages = array_map(function ($issue) {
                return $issue->getMessage();
            }, $issues);

            $this->setJsonContent(['result' => 'error', 'messages' => array_values($messages)]);

            return $this->getResult();
        }

        if ($this->isRealtimeProcessFromOldGrid()) {
            ['result' => $resultStatus, 'action_id' => $logsActionId] = $this->actionService->runList($products);
        } else {
            ['result' => $resultStatus, 'action_id' => $logsActionId] = $this->actionService->scheduleList($products);
        }

        $this->setJsonContent(['result' => $resultStatus, 'action_id' => $logsActionId]);

        return $this->getResult();
    }

    /**
     * @param \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue[] $issues
     *
     * @return \M2E\TikTokShop\Model\TikTokShop\Connector\Shop\Issue[]
     */
    private function getUniqueIssues(
        array $issues
    ): array {
        $result = [];
        foreach ($issues as $issue) {
            $result[$issue->getType()] = $issue;
        }

        return $result;
    }
}
