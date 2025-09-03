<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class TikTokShopProductId extends Column
{
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->shopRepository = $shopRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $ttsProductId = $row['tts_product_id'];
            $shopRegion = $this->shopRepository->get((int)$row['shop_id'])->getRegion()->getRegionCode();

            $url = \M2E\TikTokShop\Model\Product::getProductLinkOnChannel($ttsProductId, $shopRegion);

            $row['tts_product_id'] = sprintf('<a href="%s" target="_blank">%s</a>', $url, $ttsProductId);
        }

        return $dataSource;
    }
}
