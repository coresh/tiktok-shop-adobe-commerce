<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Account\Component\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Title extends Column
{
    private \M2E\TikTokShop\Model\Shop\RegionCollection $regionCollection;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \M2E\TikTokShop\Model\Shop\RegionCollection $regionCollection,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->regionCollection = $regionCollection;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $accountTitle = $row['title'];
            $sellerName = $row['seller_name'];
            $regionNames = $row['shop_region_codes'] ? $this->getRegionNames($row['shop_region_codes']) : '';

            $html = sprintf('<p>%s</p>', \M2E\Core\Helper\Data::escapeHtml($accountTitle));

            $html .= !empty($sellerName) ? $this->renderLine((string)\__('Seller Name'), $sellerName) : '';
            $html .= $this->renderLine((string)\__('Region(-s)'), $regionNames);

            $row['title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf(
            '<p style="margin: 0"><b>%s</b>: %s</p>',
            $label,
            \M2E\Core\Helper\Data::escapeHtml($value)
        );
    }

    private function getRegionNames(string $regionCodes): string
    {
        $result = [];
        $regions = explode(';', $regionCodes);
        foreach ($regions as $regionCode) {
            $result[] = $this->regionCollection->getByCode($regionCode)->getLabel();
        }

        return implode(', ', $result);
    }
}
