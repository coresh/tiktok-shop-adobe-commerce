<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

use M2E\TikTokShop\Controller\Adminhtml\AbstractOrder;

class UnAssignFromMagentoProduct extends AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\TikTokShop\Model\Order\Item\ProductAssignService $productAssignService;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Model\Order\Item\ProductAssignService $productAssignService,
        $context = null
    ) {
        parent::__construct($context);
        $this->orderItemRepository = $orderItemRepository;
        $this->productAssignService = $productAssignService;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $orderItemIds = explode(',', $this->getRequest()->getParam('order_item_ids', ''));

        $orderItems = $this->orderItemRepository->getByIds($orderItemIds);

        if (count($orderItems) === 0) {
            $this->setJsonContent(['error' => __('Please specify Required Options.')]);

            return $this->getResult();
        }

        $this->productAssignService->unAssign($orderItems);

        $this->setJsonContent([
            'success' => __('Item was Unlinked.'),
        ]);

        return $this->getResult();
    }
}
