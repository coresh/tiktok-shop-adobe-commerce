<?php

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Form;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Render extends \Magento\Backend\Block\Template implements RendererInterface
{
    private \M2E\TikTokShop\Helper\Magento\Carriers $magentoCarriersHelper;
    private \M2E\TikTokShop\Model\ShippingProvider\Repository $shippingProviderRepository;

    public function __construct(
        \M2E\TikTokShop\Model\ShippingProvider\Repository $shippingProviderRepository,
        \M2E\TikTokShop\Helper\Magento\Carriers $magentoCarriersHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        ?\Magento\Framework\Json\Helper\Data $jsonHelper = null,
        ?\Magento\Directory\Helper\Data $directoryHelper = null
    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->magentoCarriersHelper = $magentoCarriersHelper;
        $this->shippingProviderRepository = $shippingProviderRepository;
    }

    protected $_template = 'M2E_TikTokShop::tiktokshop/template/shipping_provider/account_mapping.phtml';
    private \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Form\Element\ShippingProviderMapping $element;

    /**
     * @param \M2E\TikTokShop\Block\Adminhtml\TikTokShop\Account\Edit\Form\Element\ShippingProviderMapping $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->element = $element;

        return $this->toHtml();
    }

    public function getAccount(): \M2E\TikTokShop\Model\Account
    {
        return $this->element->getAccount();
    }

    /**
     * @return array{code: string, name: string, required: bool}
     */
    public function getMagentoCarriers(): array
    {
        $default = [
            'code' => 'default',
            'name' => __('Default'),
            'required' => true,
            'tooltip' => __('Unless a specific TTS Carrier is explicitly assigned for a Magento Carrier, ' .
                'the TTS Carrier value selected for the \'Default\' Magento carrier will be used.'),
        ];

        return array_merge(
            [$default],
            array_map(function ($carrier) {
                return [
                    'name' => $carrier->getConfigData('title'),
                    'code' => $carrier->getCarrierCode(),
                    'required' => false,
                    'tooltip' => '',
                ];
            }, $this->magentoCarriersHelper->getCarriersWithAvailableTracking())
        );
    }

    /**
     * @return array{label: string, value: string}
     */
    public function getShippingProviders(
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\Warehouse $warehouse
    ): array {
        $shippingProviders = $this
            ->shippingProviderRepository
            ->getByAccountShopWarehouse($this->getAccount(), $shop, $warehouse);

        if (count($shippingProviders) === 0) {
            return [];
        }

        $result = [
            'default' => [
                'label' => __('None'),
                'value' => '',
            ],
        ];

        foreach ($shippingProviders as $item) {
            $result[$item->getShippingProviderId()] = [
                'label' => $item->getShippingProviderName(),
                'value' => $item->getShippingProviderId(),
            ];
        }

        return array_values($result);
    }

    public function renderTableRow(
        int $index,
        \M2E\TikTokShop\Model\Shop $shop,
        \M2E\TikTokShop\Model\Warehouse $warehouse,
        array $magentoCarrier
    ): string {
        $ttsOptions = $this->getShippingProviders($shop, $warehouse);

        if (count($ttsOptions) === 0) {
            return '';
        }

        $tableColumns = [
            sprintf('<td>%s</td>', $shop->getShopName()),
            sprintf('<td>%s</td>', $warehouse->getName()),
        ];

        $tableColumns[] = sprintf(
            '<td><span class="%s">%s</span>%s</td>',
            $magentoCarrier['required'] ? 'required-field' : '',
            $magentoCarrier['name'],
            $this->renderTooltip($magentoCarrier['tooltip'])
        );

        $selectHtml = sprintf(
            '<select name="%s" class="admin__control-select" style="width:100%%" %s>',
            $this->makeOptionName($warehouse, $magentoCarrier['code']),
            $magentoCarrier['required'] ? 'required' : '',
        );
        foreach ($ttsOptions as $option) {
            $isSelected = $this->isSelected(
                $warehouse,
                $magentoCarrier['code'],
                $option['value']
            );

            $selectHtml .= sprintf(
                '<option value="%s"%s>%s</option>',
                $option['value'],
                $isSelected ? ' selected' : '',
                $option['label']
            );
        }
        $selectHtml .= '</select>';

        $tableColumns[] = sprintf('<td>%s</td>', $selectHtml);

        return sprintf(
            '<tr class="%s %s">%s</tr>',
            $index % 2 ? '_odd-row' : '',
            $index === 0 ? 'new-shop-row' : '',
            implode('', $tableColumns)
        );
    }

    private function makeOptionName(
        \M2E\TikTokShop\Model\Warehouse $warehouse,
        string $carrierCode
    ): string {
        return sprintf(
            'shipping_provider_mapping[%s][%s]',
            $warehouse->getWarehouseId(),
            $carrierCode
        );
    }

    private function isSelected(
        \M2E\TikTokShop\Model\Warehouse $warehouse,
        string $carrierCode,
        string $shippingProviderId
    ): bool {
        $existMappings = $this->element->getExistShippingProviderMapping();

        if (!isset($existMappings[$warehouse->getWarehouseId()][$carrierCode])) {
            return false;
        }

        return $existMappings[$warehouse->getWarehouseId()][$carrierCode] === $shippingProviderId;
    }

    private function renderTooltip(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        return <<<HTML
<div class="TikTokShop-field-tooltip TikTokShop-field-tooltip-right TikTokShop-fieldset-tooltip admin__field-tooltip">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content">$text</div>
</div>
HTML;
    }
}
