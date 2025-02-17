<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Unmanaged\Column;

use M2E\TikTokShop\Model\Product;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $statusTitle = $this->mapStatus((int)$row['status']);
            $row['status'] = $statusTitle;
        }

        return $dataSource;
    }

    private function mapStatus(int $status): string
    {
        if ($status === Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Product::getStatusTitle(Product::STATUS_NOT_LISTED) . '</span>';
        }

        if ($status === Product::STATUS_LISTED) {
            return '<span style="color: green;">' . Product::getStatusTitle(Product::STATUS_LISTED) . '</span>';
        }

        if ($status === Product::STATUS_INACTIVE) {
            return '<span style="color: red;">' . Product::getStatusTitle(Product::STATUS_INACTIVE) . '</span>';
        }

        if ($status === Product::STATUS_BLOCKED) {
            return '<span style="color: orange;">' . Product::getStatusTitle(Product::STATUS_BLOCKED) . '</span>';
        }

        return '';
    }
}
