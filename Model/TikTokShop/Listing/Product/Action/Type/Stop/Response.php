<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Stop;

class Response extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\AbstractResponse
{
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(\M2E\TikTokShop\Model\Product\Repository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        $this->getListingProduct()
             ->setStatusInactive($this->getStatusChanger());

        foreach ($this->getListingProduct()->getVariants() as $variant) {
            if ($variant->isStatusListed()) {
                $variant->changeStatusToInactive();

                $this->productRepository->saveVariantSku($variant);
            }
        }

        $this->productRepository->save($this->getListingProduct());
    }
}
