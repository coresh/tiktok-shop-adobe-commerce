<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

class MagentoProductId extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \Magento\Framework\UrlInterface $url;
    private \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration;
    private \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory;

    public function __construct(
        \M2E\TikTokShop\Helper\Module\Configuration $moduleConfiguration,
        \M2E\TikTokShop\Model\Magento\ProductFactory $magentoProductFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->moduleConfiguration = $moduleConfiguration;
        $this->url = $url;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            if (empty($row['entity_id'])) {
                $row['entity_id'] = __('N/A');
                continue;
            }

            $storeId = (int)$row['listing_store_id'];
            $magentoProductId = (int)$row['entity_id'];
            $magentoProductUrl = $this->generateMagentoProductUrl(
                $magentoProductId,
                $storeId,
            );

            $withoutImageHtml = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $magentoProductUrl,
                $row['entity_id']
            );

            $row['entity_id'] = $withoutImageHtml;
            if (!$this->moduleConfiguration->getViewShowProductsThumbnailsMode()) {
                continue;
            }

            $magentoProduct = $this->magentoProductFactory->createByProductId($magentoProductId);
            $magentoProduct->setStoreId($storeId);

            $thumbnail = $magentoProduct->getThumbnailImage();
            if ($thumbnail === null) {
                continue;
            }

            $row['entity_id'] = <<<HTML
<a href="{$magentoProductUrl}" target="_blank">
    {$magentoProductId}
    <div style="margin-top: 5px"><img style="max-width: 100px; max-height: 100px;" src="{$thumbnail->getUrl()}" /></div>
</a>
HTML;
        }

        return $dataSource;
    }

    private function generateMagentoProductUrl(int $entityId, int $storeId): string
    {
        return $this->url->getUrl('catalog/product/edit', ['id' => $entityId, 'store' => $storeId]);
    }
}
