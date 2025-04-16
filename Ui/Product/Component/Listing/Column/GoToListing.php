<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

class GoToListing extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\TikTokShop\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Core\Helper\Magento\Assets $magentoAssets;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Core\Helper\Magento\Assets $magentoAssets,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
        $this->urlHelper = $urlHelper;
        $this->magentoAssets = $magentoAssets;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $product = $this->productUiRuntimeStorage->findProduct((int)$row['product_id']);
            if (empty($product)) {
                continue;
            }

            $urlData = [
                'back' => $this->urlHelper->makeBackUrlParam('m2e_tiktokshop/product_grid/allItems'),
                'id' => $product->getListingId(),
                'view_mode' => \M2E\TikTokShop\Block\Adminhtml\Listing\View\Switcher::VIEW_MODE_TIKTOKSHOP,
            ];

            $filters = [
                'product_id' => [
                    'from' => $product->getMagentoProductId(),
                    'to' => $product->getMagentoProductId(),
                ],
            ];

            $image = sprintf(
                '<img src="%s" />',
                $this->magentoAssets->getViewFileUrl('M2E_TikTokShop::images/goto_listing.png'),
            );

            $html = sprintf(
                '<div style="float:right; margin:5px 15px 0 0;"><a title="%s" target="_blank" href="%s">%s</a></div>',
                __('Go to Listing'),
                $this->urlHelper->getUrlWithFilter('m2e_tiktokshop/tiktokshop_listing/view/', $filters, $urlData),
                $image,
            );

            $row['go_to_listing'] = $html;
        }

        return $dataSource;
    }
}
