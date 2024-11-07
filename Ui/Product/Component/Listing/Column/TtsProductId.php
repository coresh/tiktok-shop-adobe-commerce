<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

class TtsProductId extends \Magento\Ui\Component\Listing\Columns\Column
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

            $row['product_tts_product_id'] = __('N/A');

            $ttsProductId = $product->getTTSProductId();
            $shopRegion = $row['shop_region'] ?? null;

            if ($product->isStatusNotListed()) {
                $row['product_tts_product_id'] = sprintf('<span style="color: gray;">%s</span>', __('Not Listed'));
            }

            if ($ttsProductId === '') {
                continue;
            }

            $url = \M2E\TikTokShop\Model\Product::getProductLinkOnChannel($ttsProductId, $shopRegion);

            $row['product_tts_product_id'] = sprintf('<a href="%s" target="_blank">%s</a>', $url, $ttsProductId);
        }

        return $dataSource;
    }
}
