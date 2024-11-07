<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Order;

class AssignToMagentoProduct extends \M2E\TikTokShop\Controller\Adminhtml\AbstractOrder
{
    public const MAPPING_PRODUCT = 'product';
    public const MAPPING_OPTIONS = 'options';

    private \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Order\Item\Repository $orderItemRepository,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->orderItemRepository = $orderItemRepository;
    }

    public function execute()
    {
        $orderItemIds = explode(',', $this->getRequest()->getParam('order_item_ids', ''));

        $orderItems = $this->orderItemRepository->getByIds($orderItemIds);

        if (count($orderItems) === 0) {
            $this->setJsonContent([
                'error' => __('Order Items does not exist.'),
            ]);

            return $this->getResult();
        }

        $firstOrderItem = reset($orderItems);

        if (
            $firstOrderItem->getMagentoProductId() === null
            || !$firstOrderItem->getMagentoProduct()->exists()
        ) {
            $block = $this
                ->getLayout()
                ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Order\Item\Product\Mapping::class);

            $this->setJsonContent([
                'title' => (string)__('Linking Product "%title"', ['title' => $firstOrderItem->getTitle()]),
                'html' => $block->toHtml(),
                'type' => self::MAPPING_PRODUCT,
            ]);

            return $this->getResult();
        }

        if ($firstOrderItem->getMagentoProduct()->isProductWithVariations()) {
            $block = $this
                ->getLayout()
                ->createBlock(
                    \M2E\TikTokShop\Block\Adminhtml\Order\Item\Product\Options\Mapping::class,
                    '',
                    ['orderItems' => $orderItems]
                );

            $this->setJsonContent([
                'title' => (string)__('Setting Product Options'),
                'html' => $block->toHtml(),
                'type' => self::MAPPING_OPTIONS,
            ]);

            return $this->getResult();
        }

        $this->setJsonContent([
            'error' => __('Product does not have Required Options.'),
        ]);

        return $this->getResult();
    }

    protected function getCustomViewNick(): string
    {
        return \M2E\TikTokShop\Helper\View\TikTokShop::NICK;
    }
}
