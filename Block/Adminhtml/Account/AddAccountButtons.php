<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Account;

class AddAccountButtons implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    private \Magento\Backend\Model\UrlInterface $urlBuilder;
    private \M2E\TikTokShop\Model\Shop\Region\AddAccountButtonOptionsProvider $addAccountButtonOptionsProvider;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\TikTokShop\Model\Shop\Region\AddAccountButtonOptionsProvider $addAccountButtonOptionsProvider
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->addAccountButtonOptionsProvider = $addAccountButtonOptionsProvider;
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
        foreach ($this->addAccountButtonOptionsProvider->retrieve() as $option) {
            $res[$option['id']] = [
                'label' => $option['label'],
                'id' => $option['id'],
                'onclick' => 'setLocation(this.getAttribute("data-url"))',
                'data_attribute' => [
                    'url' => $this->urlBuilder->getUrl(
                        '*/tiktokshop_account/beforeGetToken',
                        ['_current' => true, 'region' => $option['region_code']]
                    ),
                ],
            ];
        }

        return $res;
    }
}
