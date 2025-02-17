<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Unmanaged\Column;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Price extends Column
{
    private CurrencyInterface $localeCurrency;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CurrencyInterface $localeCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$row) {
            $unmanagedProduct = $this->unmanagedRepository->get((int)$row['id']);
            $currencyCode = $unmanagedProduct->getCurrency();
            $isSimple = (int)$row['is_simple'] === 1;

            if ($isSimple) {
                $price = $unmanagedProduct->getPrice();
                if (empty($price)) {
                    $row['price'] = __('N/A');

                    continue;
                }

                if ($price <= 0) {
                    $row['price'] = '<span style="color: #f00;">0</span>';

                    continue;
                }

                $row['price'] = $this->localeCurrency
                    ->getCurrency($currencyCode)
                    ->toCurrency($price);

                continue;
            }

            $onlineMinPrice = $row['min_price'];
            $onlineMaxPrice = $row['max_price'];

            if ($onlineMinPrice === null && $onlineMaxPrice === null) {
                $row['price'] = __('N/A');

                continue;
            }

            if (
                (!empty($onlineMinPrice) && empty($onlineMaxPrice))
                || $onlineMinPrice === $onlineMaxPrice
            ) {
                $row['price'] = $this->localeCurrency
                    ->getCurrency($currencyCode)
                    ->toCurrency($onlineMinPrice);

                continue;
            }

            if (
                $onlineMaxPrice !== null
                && $onlineMinPrice === null
            ) {
                $row['price'] = $this->localeCurrency
                    ->getCurrency($currencyCode)
                    ->toCurrency($onlineMaxPrice);

                continue;
            }

            $formattedMinPrice = $this->localeCurrency
                ->getCurrency($currencyCode)
                ->toCurrency($onlineMinPrice);

            $formattedMaxPrice = $this->localeCurrency
                ->getCurrency($currencyCode)
                ->toCurrency($onlineMaxPrice);

            $row['price'] = sprintf('%s - %s', $formattedMinPrice, $formattedMaxPrice);
        }

        return $dataSource;
    }
}
