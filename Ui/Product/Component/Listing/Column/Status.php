<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Ui\Product\Component\Listing\Column;

use M2E\TikTokShop\Model\Product;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    private \M2E\TikTokShop\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage;
    private \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Product\Ui\RuntimeStorage $productUiRuntimeStorage,
        \M2E\TikTokShop\Model\ScheduledAction\Repository $scheduledActionRepository,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productUiRuntimeStorage = $productUiRuntimeStorage;
        $this->scheduledActionRepository = $scheduledActionRepository;
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

            $html = '';
            $html .= $this->getCurrentStatus($product);
            $html .= $this->getScheduledTag($product);

            $row['product_status'] = $html;
        }

        return $dataSource;
    }

    private function getCurrentStatus(\M2E\TikTokShop\Model\Product $product): string
    {
        if ($product->isStatusNotListed()) {
            return '<span style="color: gray;">' . Product::getStatusTitle(Product::STATUS_NOT_LISTED) . '</span>';
        }

        if ($product->isStatusListed()) {
            return '<span style="color: green;">' . Product::getStatusTitle(Product::STATUS_LISTED) . '</span>';
        }

        if ($product->isStatusInactive()) {
            return '<span style="color: red;">' . Product::getStatusTitle(Product::STATUS_INACTIVE) . '</span>';
        }

        if ($product->isStatusBlocked()) {
            return '<span style="color: orange;">' . Product::getStatusTitle(Product::STATUS_BLOCKED) . '</span>';
        }

        return '';
    }

    private function getScheduledTag(\M2E\TikTokShop\Model\Product $product): string
    {
        $scheduledAction = $this->scheduledActionRepository->findByListingProductId($product->getId());
        if ($scheduledAction === null) {
            return '';
        }

        $html = '';

        switch ($scheduledAction->getActionType()) {
            case \M2E\TikTokShop\Model\Product::ACTION_LIST:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case \M2E\TikTokShop\Model\Product::ACTION_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case \M2E\TikTokShop\Model\Product::ACTION_REVISE:
                $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                break;

            case \M2E\TikTokShop\Model\Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case \M2E\TikTokShop\Model\Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }
}
