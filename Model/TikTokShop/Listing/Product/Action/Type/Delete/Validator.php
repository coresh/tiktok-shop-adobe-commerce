<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Delete;

class Validator implements \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ValidatorInterface
{
    use \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\ValidatorTrait;

    private \M2E\TikTokShop\Model\Product\RemoveHandler $removeHandler;

    public function __construct(\M2E\TikTokShop\Model\Product\RemoveHandler $removeHandler)
    {
        $this->removeHandler = $removeHandler;
    }

    public function validate(
        \M2E\TikTokShop\Model\Product $product,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Configurator $actionConfigurator,
        \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\VariantSettings $variantSettings
    ): bool {
        if (!$product->isRetirable()) {
            $this->removeHandler->process($product, \M2E\Core\Helper\Data::INITIATOR_UNKNOWN);

            return false;
        }

        return true;
    }
}
