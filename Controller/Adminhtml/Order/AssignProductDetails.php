<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

use M2E\TikTokShop\Controller\Adminhtml\AbstractOrder;

class AssignProductDetails extends AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\TikTokShop\Model\Order\Item\DetailsAssignService $detailsAssignService;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Model\Order\Item\DetailsAssignService $detailsAssignService,
        $context = null
    ) {
        parent::__construct($context);
        $this->orderItemRepository = $orderItemRepository;
        $this->detailsAssignService = $detailsAssignService;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function execute()
    {
        $orderItemIds = explode(',', $this->getRequest()->getParam('order_item_ids', ''));

        $orderItems = $this->orderItemRepository->getByIds($orderItemIds);
        $optionsData = $this->getProductOptionsDataFromPost();

        if (count($optionsData) == 0 || count($orderItems) === 0) {
            $this->setJsonContent(['error' => (string)__('Please specify Required Options.')]);

            return $this->getResult();
        }

        try {
            $this->detailsAssignService->assign(
                $orderItems,
                $optionsData,
                \M2E\TikTokShop\Helper\Data::INITIATOR_USER
            );
        } catch (\Throwable $exception) {
            $this->setJsonContent(['error' => $exception->getMessage()]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'success' => __('Order Item Options were configured.'),
        ]);

        return $this->getResult();
    }
}
