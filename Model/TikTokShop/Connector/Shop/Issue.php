<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Connector\Shop;

class Issue
{
    private string $type;
    private string $message;

    public function __construct(
        string $type,
        string $message
    ) {
        $this->type = $type;
        $this->message = $message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMessage(): string
    {
        switch ($this->type) {
            case 'PRODUCT_QUANTITY_LIMIT':
                return 'Product was not listed. The daily limit of 500 listings for new sellers has been exceeded';
            case 'RETURN_WAREHOUSE':
                return 'Product was not listed. The Return Warehouse information must be specified for this Shop in the Seller Center.';
            case 'SHOP_TAX':
                return 'Product was not listed. The Tax information must be specified for this Shop in the Seller Center.';
            default:
                return $this->message;
        }
    }
}
