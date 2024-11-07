<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Grid\Column\Renderer;

class TtsProductId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected string $dataKeyStatus = 'status';
    protected string $dataKeyProductId = 'product_id';
    protected string $dataKeyShopRegion = 'shop_region';

    public function render(\Magento\Framework\DataObject $row): string
    {
        $productStatus = $this->getStatus($row);
        if ($productStatus === \M2E\TikTokShop\Model\Product::STATUS_NOT_LISTED) {
            return sprintf('<span style="color: gray;">%s</span>', __('Not Listed'));
        }

        $ttsProductId = $this->getTtsProductId($row);
        $region = $this->getRegion($row);
        $url = \M2E\TikTokShop\Model\Product::getProductLinkOnChannel($ttsProductId, $region);

        if (empty($url)) {
            return (string)__('N/A');
        }

        return sprintf('<a href="%s" target="_blank">%s</a>', $url, $ttsProductId);
    }

    public function renderExport(\Magento\Framework\DataObject $row): string
    {
        return $this->getTtsProductId($row);
    }

    private function getTtsProductId(\Magento\Framework\DataObject $row): string
    {
        return (string)($row->getData($this->dataKeyProductId) ?? '');
    }

    private function getRegion(\Magento\Framework\DataObject $row): string
    {
        return (string)($row->getData($this->dataKeyShopRegion) ?? '');
    }

    private function getStatus(\Magento\Framework\DataObject $row): int
    {
        return (int)($row->getData($this->dataKeyStatus) ?? 0);
    }
}
