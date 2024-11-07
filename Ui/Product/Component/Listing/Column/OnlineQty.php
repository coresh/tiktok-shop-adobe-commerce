<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

class OnlineQty extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\TikTokShop\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $product = $this->productUiRuntimeStorage->findProduct((int)$row['product_id']);
            if (empty($product)) {
                continue;
            }

            if ($product->isStatusNotListed()) {
                $row['product_online_qty'] = sprintf(
                    '<span style="color: gray">%s</span>',
                    __('Not Listed')
                );
            } elseif ($product->isStatusInactive()) {
                $row['product_online_qty'] = sprintf(
                    '<span style="color: gray; text-decoration: line-through;">%s</span>',
                    $product->getOnlineQty()
                );
            } else {
                $row['product_online_qty'] = $product->getOnlineQty();
            }
        }

        return $dataSource;
    }
}
