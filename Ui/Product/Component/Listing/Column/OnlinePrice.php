<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

class OnlinePrice extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\TikTokShop\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage;
    private \Magento\Framework\Locale\CurrencyInterface $localeCurrency;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
        $this->localeCurrency = $localeCurrency;
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

            if ($product->isStatusNotListed()) {
                $row['product_online_price'] = sprintf('<span style="color: gray;">%s</span>', __('Not Listed'));

                continue;
            }

            $onlinePrice = $this->formatOnlinePrice(
                $product->getMinPrice(),
                $product->getMaxPrice(),
                \M2E\TikTokShop\Model\Shop::getCurrencyCodeByRegion($row['shop_region'])
            );

            if ($product->isStatusInactive()) {
                $row['product_online_price'] =  sprintf(
                    '<span style="color: gray; text-decoration: line-through;">%s</span>',
                    $onlinePrice
                );
            } else {
                $row['product_online_price'] = $onlinePrice;
            }
        }

        return $dataSource;
    }

    private function formatOnlinePrice(?float $onlineMinPrice, ?float $onlineMaxPrice, string $currencyCode): string
    {
        if ($onlineMinPrice === null && $onlineMaxPrice === null) {
            return (string)__('N/A');
        }

        if (
            ($onlineMinPrice !== null && $onlineMaxPrice === null)
            || $onlineMinPrice === $onlineMaxPrice
        ) {
            return $this->localeCurrency
                ->getCurrency($currencyCode)
                ->toCurrency($onlineMinPrice);
        }

        /** @psalm-suppress RedundantCondition */
        if (
            $onlineMaxPrice !== null
            && $onlineMinPrice === null
        ) {
            return $this->localeCurrency
                ->getCurrency($currencyCode)
                ->toCurrency($onlineMaxPrice);
        }

        $formattedMinPrice = $this->localeCurrency
            ->getCurrency($currencyCode)
            ->toCurrency($onlineMinPrice);

        $formattedMaxPrice = $this->localeCurrency
            ->getCurrency($currencyCode)
            ->toCurrency($onlineMaxPrice);

        return sprintf('%s - %s', $formattedMinPrice, $formattedMaxPrice);
    }
}
