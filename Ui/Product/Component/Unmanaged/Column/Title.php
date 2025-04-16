<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Title extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;
    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->shopRepository = $shopRepository;
        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $productTitle = $row['title'];

            $html = sprintf('<p>%s</p>', $productTitle);

            $sku = $this->getSku($row);
            if (!empty($sku)) {
                $html .= $this->renderLine((string)\__('SKU'), $sku);
            }

            $categoriesPath = $this->getCategoriesPath($row['categories_data'] ?? []);
            if (!empty($categoriesPath)) {
                $html .= $this->renderLine((string)\__('Category'), $categoriesPath);
            }

            $shopName = $this->getShopName((int)$row['shop_id']);
            $html .= $this->renderLine((string)\__('Shop'), $shopName);

            $salesAttributesHtml = $this->renderSalesAttributes($row);
            if (!empty($salesAttributesHtml)) {
                $html .= $salesAttributesHtml;
            }

            $row['title'] = $html;
        }

        return $dataSource;
    }

    private function renderLine(string $label, string $value): string
    {
        return sprintf('<p style="margin: 0"><strong>%s:</strong> %s</p>', $label, $value);
    }

    private function getShopName(int $shopId): string
    {
        return $this->shopRepository->get($shopId)->getShopNameWithRegion();
    }

    private function getUnmanagedProduct(int $id): \M2E\TikTokShop\Model\UnmanagedProduct
    {
        return $this->unmanagedRepository->get($id);
    }

    private function getSku(array $row): string
    {
        $unmanagedProduct = $this->getUnmanagedProduct((int)$row['id']);

        return \M2E\Core\Helper\Data::escapeHtml(!$unmanagedProduct->isSimple() ? '' : $unmanagedProduct->getSku());
    }

    private function getCategoriesPath(string $categoriesDataJson): string
    {
        if (empty($categoriesDataJson)) {
            return '';
        }

        $categoriesData = json_decode($categoriesDataJson, true);
        if (!is_array($categoriesData)) {
            return '';
        }

        $parts = array_map(function (array $category) {
            $categoryName = $category['local_name'] ?? '';
            if (!empty($category['is_leaf'])) {
                return sprintf('%s (%s)', $categoryName, $category['id'] ?? '');
            }
            return $categoryName;
        }, $categoriesData);

        return \M2E\Core\Helper\Data::escapeHtml(implode(' >> ', $parts));
    }

    private function renderSalesAttributes(array $row): string
    {
        $unmanagedProduct = $this->getUnmanagedProduct((int)$row['id']);
        if ($unmanagedProduct->isSimple()) {
            return '';
        }

        $salesAttributes = $unmanagedProduct->getSalesAttributeNames();
        if (empty($salesAttributes)) {
            return '';
        }

        $configurableAttributes = array_map(
            static function (string $attributeName) {
                return sprintf('<span>%s</span>', $attributeName);
            },
            $salesAttributes
        );

        return sprintf(
            '<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 0 7px">%s</div>',
            implode(', ', $configurableAttributes)
        );
    }
}
