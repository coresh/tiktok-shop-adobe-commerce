<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Relist;

class Response extends \M2E\TikTokShop\Model\TikTokShop\Listing\Product\Action\Type\Revise\Response
{
    private \M2E\TikTokShop\Model\Product\Repository $productRepository;

    public function __construct(\M2E\TikTokShop\Model\Product\Repository $productRepository)
    {
        parent::__construct($productRepository);
        $this->productRepository = $productRepository;
    }

    public function processSuccess(array $response, array $responseParams = []): void
    {
        $this->getListingProduct()
             ->setStatusListed($response['product_id'], $this->getStatusChanger())
             ->removeBlockingByError();

        $this->productRepository->save($this->getListingProduct());

        parent::processSuccess($response, $responseParams);
    }
}
