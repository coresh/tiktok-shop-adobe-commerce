<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\Account;

class AddAccountButtons implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    private \Magento\Backend\Model\UrlInterface $urlBuilder;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    public function getButtonData()
    {
        return [
            'id' => 'add-tts-account',
            'label' => __('Add Account'),
            'class' => 'add-tts-account',
            'style' => 'pointer-events: none',
            'class_name' => \M2E\TikTokShop\Block\Adminhtml\Magento\Button\SplitButton::class,
            'options' => [
                'gb' => [
                    'label' => __('United Kingdom'),
                    'id' => 'gb',
                    'onclick' => 'setLocation(this.getAttribute("data-url"))',
                    'data_attribute' => [
                        'url' => $this->urlBuilder->getUrl(
                            '*/tiktokshop_account/beforeGetToken',
                            ['_current' => true, 'region' => \M2E\TikTokShop\Model\Shop::REGION_GB]
                        ),
                    ],
                ],
                'us' => [
                    'label' => __('United States'),
                    'id' => 'gb',
                    'onclick' => 'setLocation(this.getAttribute("data-url"))',
                    'data_attribute' => [
                        'url' => $this->urlBuilder->getUrl(
                            '*/tiktokshop_account/beforeGetToken',
                            ['_current' => true, 'region' => \M2E\TikTokShop\Model\Shop::REGION_US]
                        ),
                    ],
                ],
                'es' => [
                    'label' => __('Spain'),
                    'id' => 'es',
                    'onclick' => 'setLocation(this.getAttribute("data-url"))',
                    'data_attribute' => [
                        'url' => $this->urlBuilder->getUrl(
                            '*/tiktokshop_account/beforeGetToken',
                            ['_current' => true, 'region' => \M2E\TikTokShop\Model\Shop::REGION_ES]
                        ),
                    ],
                ],
            ],
        ];
    }
}
