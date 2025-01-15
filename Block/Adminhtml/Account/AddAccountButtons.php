<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Account;

class AddAccountButtons implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \M2E\TikTokShop\Model\Shop\RegionCollection $regionCollection;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\TikTokShop\Model\Shop\RegionCollection $regionCollection
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->regionCollection = $regionCollection;
    }

    public function getButtonData()
    {
        return [
            'id' => 'add-tts-account',
            'label' => __('Add Account'),
            'class' => 'add-tts-account',
            'style' => 'pointer-events: none',
            'class_name' => \M2E\TikTokShop\Block\Adminhtml\Magento\Button\SplitButton::class,
            'options' => $this->getDropdownOptions(),
        ];
    }

    private function getDropdownOptions(): array
    {
        $res = [];
        foreach ($this->regionCollection->getAll() as $region) {
            $id = mb_strtolower($region->getRegionCode());

            $res[$id] = [
                'label' => $region->getLabel(),
                'id' => $id,
                'onclick' => 'setLocation(this.getAttribute("data-url"))',
                'data_attribute' => [
                    'url' => $this->urlBuilder->getUrl(
                        '*/tiktokshop_account/beforeGetToken',
                        ['_current' => true, 'region' => $region->getRegionCode()]
                    ),
                ],
            ];
        }

        return $res;
    }
}
