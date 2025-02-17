<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class MagentoProductId extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \Magento\Framework\UrlInterface $urlBuilder;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $magentoProductId = $row['magento_product_id'];
            $html = '';

            $productTitle =  $row['title'];
            if (strlen($productTitle) > 60) {
                $productTitle = substr($productTitle, 0, 60) . '...';
            }
            $productTitle = \M2E\TikTokShop\Helper\Data::escapeHtml($productTitle);
            $productTitle = \M2E\TikTokShop\Helper\Data::escapeJs($productTitle);

            if ($magentoProductId === null) {
                $link = sprintf(
                    '<a href="javascript:void(0);" id="row_link_%s" class="action-link" data-id="%s" data-title="%s" data-url="%s">%s</a>',
                    (int)$row['id'],
                    (int)$row['id'],
                    $productTitle,
                    $this->urlBuilder->getUrl('m2e_tiktokshop/product_unmanaged_mapping/mapProductPopupHtml', ['account_id' => (int)$row['account_id']]),
                    __('Link')
                );

                $html .= $link;
            } else {
                $magentoProductUrl = $this->generateMagentoProductUrl((int)$magentoProductId);
                $move = sprintf(
                    '<a href="javascript:void(0);" id="row_move_%s" class="action-link" data-id="%s" data-url_move="%s" data-url_grid="%s" data-url_new_listing="%s">%s</a>',
                    (int)$row['id'],
                    (int)$row['id'],
                    $this->urlBuilder->getUrl('m2e_tiktokshop/product_unmanaged_moving/prepareMoveToListing'),
                    $this->urlBuilder->getUrl('m2e_tiktokshop/product_unmanaged_moving/MoveToListingGrid'),
                    $this->urlBuilder->getUrl('m2e_tiktokshop/tiktokshop_listing_create/index'),
                    __('Move')
                );
                $html .= <<<HTML
<a href="{$magentoProductUrl}" target="_blank">{$magentoProductId}</a>
HTML;
                $html .= '&nbsp;&nbsp;&nbsp;';
                $html .= $move;
            }

            $row['magento_product_id'] = $html;
        }

        return $dataSource;
    }

    private function generateMagentoProductUrl(int $entityId): string
    {
        return $this->urlBuilder->getUrl('catalog/product/edit', ['id' => $entityId]);
    }
}
