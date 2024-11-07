<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model;

interface ProductInterface
{
    public function getListing(): Listing;

    public function getSellingFormatTemplate(): \M2E\TikTokShop\Model\Template\SellingFormat;
    public function getMagentoProduct(): \M2E\TikTokShop\Model\Magento\Product\Cache;
}
