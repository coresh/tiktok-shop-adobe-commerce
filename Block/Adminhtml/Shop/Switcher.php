<?php

namespace M2E\TikTokShop\Block\Adminhtml\Shop;

class Switcher extends \M2E\TikTokShop\Block\Adminhtml\Switcher
{
    /** @var string */
    protected $paramName = 'shop';
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->shopRepository = $shopRepository;
    }

    public function getLabel()
    {
        return (string)__('Shop');
    }

    protected function loadItems()
    {
        $shops = $this->shopRepository->getAll();

        if (count($shops) === 1) {
            $this->hasDefaultOption = false;
            $this->setIsDisabled();
        }

        $items = [];
        foreach ($shops as $shop) {
            $items['tiktokshop']['value'][] = [
                'value' => $shop->getId(),
                'label' => $shop->getShopNameWithRegion(),
            ];
        }

        $this->items = $items;
    }

    private function setIsDisabled(): void
    {
        $this->setData('is_disabled', true);
    }
}
