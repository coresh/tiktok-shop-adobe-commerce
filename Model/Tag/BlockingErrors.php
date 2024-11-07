<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Tag;

class BlockingErrors
{
    public function getList(): array
    {
        return [
            '12052348', // The product description html tag required attribute is miss.
            '12052013', // The product description cannot exceed maximum characters
            '12052116', // product package size is invalid
            '12052301', // The width and length of product image 3 {5/2/...} must be at least \n300:300, check the image URI
            '12052105', // required qualification is missing
            '12038004', // upload file type is invalid
            '12052345', // The product description html tag not support
            '12019011', // product package weight is invalid
            '12038002', // upload file is invalid (for an image)
            '12052105', // required qualification is missing (certificate)
            '36009004', // body.package_weight.value is missing
            '12052038', // Product price locked due to ongoing promotion.
        ];
    }
}
