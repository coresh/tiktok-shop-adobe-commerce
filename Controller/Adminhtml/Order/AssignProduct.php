<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

class AssignProduct extends \M2E\TikTokShop\Controller\Adminhtml\AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\TikTokShop\Model\Order\Item\ProductAssignService $productAssignService;
    private \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\TikTokShop\Model\Order\Item\ProductAssignService $productAssignService,
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->magentoProductFactory = $magentoProductFactory;
        $this->productAssignService = $productAssignService;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id', false);
        $orderItemIds = explode(',', $this->getRequest()->getParam('order_item_ids', ''));

        $orderItems = $this->orderItemRepository->getByIds($orderItemIds);

        if (
            empty($productId)
            || count($orderItems) === 0
        ) {
            $this->setJsonContent(['error' => (string)__('Please specify Required Options.')]);

            return $this->getResult();
        }

        $magentoProduct = $this->magentoProductFactory->createByProductId((int)$productId);
        $magentoProduct->setStoreId($this->getStoreIdFromOrderItems($orderItems));

        if (!$magentoProduct->exists()) {
            $this->setJsonContent(['error' => (string)__('Product does not exist.')]);

            return $this->getResult();
        }

        $this->productAssignService->assign(
            $orderItems,
            $magentoProduct->getProduct(),
            \M2E\TikTokShop\Helper\Data::INITIATOR_USER
        );

        $this->setJsonContent([
            'success' => (string)__('Order Item was Linked.'),
            'continue' => $magentoProduct->isProductWithVariations(),
        ]);

        return $this->getResult();
    }

    /**
     * @param \M2E\TikTokShop\Model\Order\Item[] $orderItems
     */
    private function getStoreIdFromOrderItems(array $orderItems): int
    {
        $storeIds = [];
        foreach ($orderItems as $orderItem) {
            $storeIds[] = $orderItem->getStoreId();
        }

        $uniqueStoreIds = array_unique($storeIds);

        return (int)reset($uniqueStoreIds);
    }
}
