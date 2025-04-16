<?php

namespace M2E\TikTokShop\Observer\Indexes;

class Disable extends \M2E\TikTokShop\Observer\AbstractObserver
{
    /** @var \M2E\TikTokShop\Model\Magento\Product\Index */
    private $productIndex;
    private \M2E\Core\Helper\Magento $helperMagento;

    public function __construct(
        \M2E\TikTokShop\Model\Magento\Product\Index $productIndex,
        \M2E\Core\Helper\Magento $helperMagento
    ) {
        $this->productIndex = $productIndex;
        $this->helperMagento = $helperMagento;
    }

    protected function process(): void
    {
        if ($this->helperMagento->isMSISupportingVersion()) {
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
