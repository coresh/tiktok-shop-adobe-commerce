<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

use M2E\TikTokShop\Model\ResourceModel\Product\Grid\AllItems\Collection as AllItemsCollection;

class IssueAffectedItems extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \Magento\Framework\UrlInterface $url;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->url = $url;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $url = $this->url->getUrl(
                'm2e_tiktokshop/product_grid/allItems',
                [
                    AllItemsCollection::FILTER_BY_ERROR_CODE_FILED_NAME => $row['error_code']
                ]
            );

            $row['total_items'] = sprintf("<a href='%s'>%s</a>", $url, $row['total_items']);
        }

        return $dataSource;
    }
}
