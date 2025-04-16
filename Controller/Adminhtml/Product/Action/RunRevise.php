<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Action;

class RunRevise extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\AbstractAction
{
    use ActionTrait;

    private \M2E\TikTokShop\Model\Product\Repository $productRepository;
    private \M2E\TikTokShop\Model\ResourceModel\Product\Grid\AllItems\ActionFilter $massActionFilter;
    /** @var \M2E\TikTokShop\Controller\Adminhtml\Product\Action\ActionService */
    private ActionService $actionService;

    public function __construct(
        \M2E\TikTokShop\Controller\Adminhtml\Product\Action\ActionService $actionService,
        \M2E\TikTokShop\Model\ResourceModel\Product\Grid\AllItems\ActionFilter $massActionFilter,
        \M2E\TikTokShop\Model\Product\Repository $productRepository
    ) {
        parent::__construct($productRepository);

        $this->productRepository = $productRepository;
        $this->massActionFilter = $massActionFilter;
        $this->actionService = $actionService;
    }

    public function execute()
    {
        $products = $this->productRepository->massActionSelectedProducts($this->massActionFilter);
        $channelTitle = \M2E\TikTokShop\Helper\Module::getChannelTitle();

        if ($this->isRealtimeAction($products)) {
            ['result' => $result] = $this->actionService->runRevise($products);
            if ($result === 'success') {
                $this->getMessageManager()->addSuccessMessage(
                    __(
                        '"Revising Selected Items On %channel_title" task has completed.',
                        [
                            'channel_title' => $channelTitle
                        ]
                    ),
                );
            } else {
                $this->getMessageManager()->addErrorMessage(
                    __(
                        '"Revising Selected Items On %channel_title" task has completed with errors.',
                        [
                            'channel_title' => $channelTitle
                        ]
                    ),
                );
            }

            return $this->redirectToGrid();
        }

        $this->actionService->scheduleRevise($products);

        $this->getMessageManager()->addSuccessMessage(
            __(
                '"Revising Selected Items On %channel_title" task has completed.',
                [
                    'channel_title' => $channelTitle
                ]
            ),
        );

        return $this->redirectToGrid();
    }
}
