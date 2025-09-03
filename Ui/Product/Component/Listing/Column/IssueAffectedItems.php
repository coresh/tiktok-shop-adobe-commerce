<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

use M2E\TikTokShop\Model\ResourceModel\Product\Grid\AllItems\Collection as AllItemsCollection;

class IssueAffectedItems extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\Core\Ui\AppliedFilters\Manager $appliedFiltersManager;

    public function __construct(
        \M2E\Core\Ui\AppliedFilters\Manager $appliedFiltersManager,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->appliedFiltersManager = $appliedFiltersManager;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $appliedFilterBuilder = new \M2E\Core\Ui\AppliedFilters\Builder();
            $appliedFilterBuilder->addSelectFilter('error_code', [$row['error_code']]);

            $url = $this->appliedFiltersManager->createUrlWithAppliedFilters(
                'm2e_tiktokshop/product_grid/allItems',
                $appliedFilterBuilder->build()
            );

            $row['total_items'] = sprintf("<a href='%s'>%s</a>", $url, $row['total_items']);
        }

        return $dataSource;
    }
}
