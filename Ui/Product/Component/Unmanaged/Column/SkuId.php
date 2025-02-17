<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class SkuId extends Column
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $unmanagedProduct = $this->unmanagedRepository->get((int)$row['id']);
            $skuId = $unmanagedProduct->isSimple() ? $unmanagedProduct->getSkuId() : '';

            $row['sku_id'] = $skuId;
        }

        return $dataSource;
    }
}
