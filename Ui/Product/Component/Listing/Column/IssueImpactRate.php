<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

class IssueImpactRate extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $row['impact_rate'] = round((float)$row['impact_rate'], 1) . '%';
        }

        return $dataSource;
    }
}
