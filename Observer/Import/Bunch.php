<?php

namespace M2E\TikTokShop\Observer\Import;

class Bunch extends \M2E\TikTokShop\Observer\AbstractObserver
{
    /** @var \M2E\TikTokShop\PublicServices\Product\SqlChange */
    private $publicService;
    /** @var \Magento\Catalog\Model\Product */
    private $magentoProduct;

    public function __construct(
        \M2E\TikTokShop\Helper\Factory $helperFactory,
        \M2E\TikTokShop\PublicServices\Product\SqlChange $publicService,
        \Magento\Catalog\Model\Product $magentoProduct
    ) {
        parent::__construct($helperFactory);
        $this->publicService = $publicService;
        $this->magentoProduct = $magentoProduct;
    }

    protected function process(): void
    {
        $rowData = $this->getEvent()->getBunch();

        $productIds = [];

        foreach ($rowData as $item) {
            if (!isset($item['sku'])) {
                continue;
            }

            $id = $this->magentoProduct->getIdBySku($item['sku']);
            if ((int)$id > 0) {
                $productIds[] = $id;
            }
        }

        foreach ($productIds as $id) {
            $this->publicService->markProductChanged($id);
        }

        $this->publicService->applyChanges();
    }
}
