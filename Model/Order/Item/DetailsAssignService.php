<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Order\Item;

class DetailsAssignService
{
    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;
    private \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Helper\Magento\Product $magentoProductHelper
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->magentoProductHelper = $magentoProductHelper;
    }

    /**
     * @param \M2E\TikTokShop\Model\Order\Item[] $orderItems
     * @param array $optionsData
     * @param int $initiator
     *
     * @return void
     */
    public function assign(array $orderItems, array $optionsData, int $initiator): void
    {
        $associatedProducts = [];
        $associatedOptions = [];

        foreach ($optionsData as $optionId => $optionData) {
            $optionId = (int)$optionId;
            $valueId = (int)$optionData['value_id'];

            $associatedProducts["$optionId::$valueId"] = $optionData['product_ids'];
            $associatedOptions[$optionId] = $valueId;
        }

        $loggedOrders = [];
        foreach ($orderItems as $orderItem) {
            $orderItem = $this->assignItem(
                $orderItem,
                $associatedProducts,
                $associatedOptions
            );

            if (!isset($loggedOrders[$orderItem->getOrderId()])) {
                $orderItem->getOrder()->getLogService()->setInitiator($initiator);
                $orderItem->getOrder()->addSuccessLog('Order Item "%title%" Options were configured.', [
                    'title' => $orderItem->getChannelProductTitle(),
                ]);
            }
            $loggedOrders[$orderItem->getOrderId()] = true;
        }
    }

    private function assignItem(
        \M2E\TikTokShop\Model\Order\Item $orderItem,
        array $associatedProducts,
        array $associatedOptions
    ): \M2E\TikTokShop\Model\Order\Item {
        $magentoProduct = $orderItem->getMagentoProduct();

        if (!$magentoProduct->exists()) {
            throw new \M2E\TikTokShop\Model\Exception\Logic('Product does not exist.');
        }

        if (
            empty($associatedProducts)
            || (!$magentoProduct->isGroupedType() && empty($associatedOptions))
        ) {
            throw new \InvalidArgumentException('Required Options were not selected.');
        }

        if ($magentoProduct->isGroupedType()) {
            $associatedOptions = [];
            $associatedProducts = reset($associatedProducts);
        }

        $associatedProducts = $this->magentoProductHelper->prepareAssociatedProducts(
            $associatedProducts,
            $magentoProduct,
        );

        $orderItem->setAssociatedProducts($associatedProducts);
        $orderItem->setAssociatedOptions($associatedOptions);

        $this->orderItemRepository->save($orderItem);

        return $orderItem;
    }
}
