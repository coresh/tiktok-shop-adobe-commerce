<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

use M2E\TikTokShop\Controller\Adminhtml\AbstractOrder;

class CheckProductOptionStockAvailability extends AbstractOrder
{
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory;
    private \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory,
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->orderItemRepository = $orderItemRepository;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->magentoProductHelper = $magentoProductHelper;
    }

    /**
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * @throws \M2E\TikTokShop\Model\Exception
     */
    public function execute()
    {
        $orderItemIds = explode(',', $this->getRequest()->getParam('order_item_ids', ''));

        $orderItems = $this->orderItemRepository->getByIds($orderItemIds);
        $optionsData = $this->getProductOptionsDataFromPost();

        if (
            count($optionsData) == 0
            || count($orderItems) === 0
        ) {
            $this->setJsonContent(['is_in_stock' => false]);

            return $this->getResult();
        }

        $associatedProducts = [];

        foreach ($optionsData as $optionId => $optionData) {
            $optionId = (int)$optionId;
            $valueId = (int)$optionData['value_id'];

            $associatedProducts["$optionId::$valueId"] = $optionData['product_ids'];
        }

        $firstOrderItem = reset($orderItems);
        $associatedProducts = $this->magentoProductHelper->prepareAssociatedProducts(
            $associatedProducts,
            $firstOrderItem->getMagentoProduct()
        );

        foreach ($associatedProducts as $productId) {
            $magentoProductTemp = $this->magentoProductFactory->createByProductId((int)$productId);

            if (!$magentoProductTemp->isStockAvailability()) {
                $this->setJsonContent(['is_in_stock' => false]);

                return $this->getResult();
            }
        }

        $this->setJsonContent(['is_in_stock' => true]);

        return $this->getResult();
    }
}
