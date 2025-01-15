<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Shop;

use M2E\TikTokShop\Model\TikTokShop\Connector\Account\Shop as ResponseShop;

class UpdateService
{
    private \M2E\TikTokShop\Model\ShopFactory $shopFactory;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \M2E\TikTokShop\Model\Shop\RegionCollection $regionCollection;

    public function __construct(
        \M2E\TikTokShop\Model\ShopFactory $shopFactory,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Shop\RegionCollection $regionCollection
    ) {
        $this->shopFactory = $shopFactory;
        $this->shopRepository = $shopRepository;
        $this->regionCollection = $regionCollection;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     * @param \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Shop[] $ttsShops
     *
     * @return void
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     */
    public function process(\M2E\TikTokShop\Model\Account $account, array $ttsShops): void
    {
        $existShops = [];
        foreach ($account->getShops() as $shop) {
            $existShops[$shop->getShopId()] = $shop;
        }

        foreach ($ttsShops as $responseShop) {
            if (isset($existShops[$responseShop->getShopId()])) {
                $existShop = $existShops[$responseShop->getShopId()];
                if (
                    $existShop->getShopName() !== $responseShop->getShopName()
                    || $existShop->getRegion() !== $responseShop->getRegion()
                    || $existShop->getType() !== $this->getType($responseShop->getType())
                ) {
                    $existShop->setShopName($responseShop->getShopName())
                              ->setRegion($this->regionCollection->getByCode($responseShop->getRegion()))
                              ->setType($this->getType($responseShop->getType()));

                    $this->shopRepository->save($existShop);
                }

                continue;
            }

            $shop = $this->shopFactory->create();
            $shop->create(
                $account,
                $responseShop->getShopId(),
                $responseShop->getShopName(),
                $this->regionCollection->getByCode($responseShop->getRegion()),
                $this->getType($responseShop->getType())
            );
            $this->shopRepository->create($shop);

            $existShops[$shop->getShopId()] = $shop;
        }

        $account->setShops(array_values($existShops));
    }

    private function getType(string $ttsType): int
    {
        $type = [
            ResponseShop::CROSS_BORDER => \M2E\TikTokShop\Model\Shop::TYPE_CROSS_BORDER,
            ResponseShop::LOCAL_TO_LOCAL => \M2E\TikTokShop\Model\Shop::TYPE_LOCAL_TO_LOCAL,
        ];

        return $type[$ttsType];
    }
}
