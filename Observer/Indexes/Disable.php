<?php

namespace M2E\TikTokShop\Observer\Indexes;

class Disable extends \M2E\TikTokShop\Observer\AbstractObserver
{
    /** @var \M2E\TikTokShop\Model\Magento\Product\Index */
    private $productIndex;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\Product\Index $productIndex,
        \M2E\TikTokShop\Helper\Factory $helperFactory
    ) {
        $this->productIndex = $productIndex;
        parent::__construct($helperFactory);
    }

    protected function process(): void
    {
        if ($this->getHelper('Magento')->isMSISupportingVersion()) {
            return;
        }

        if (!$this->productIndex->isIndexManagementEnabled()) {
            return;
        }

        foreach ($this->productIndex->getIndexes() as $code) {
            if ($this->productIndex->disableReindex($code)) {
                $this->productIndex->rememberDisabledIndex($code);
            }
        }
    }
}
