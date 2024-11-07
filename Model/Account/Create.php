<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Account;

class Create
{
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Add\Processor $addProcessor;
    private \M2E\TikTokShop\Model\Warehouse\SynchronizeService $warehouseSynchronizeService;
    private Repository $accountRepository;
    private \M2E\TikTokShop\Model\AccountFactory $accountFactory;
    private \M2E\TikTokShop\Helper\Magento\Store $storeHelper;
    private \M2E\TikTokShop\Model\ShippingProvider\SynchronizeService $shippingProviderSynchronizeService;
    private \M2E\TikTokShop\Model\Shop\UpdateService $shopUpdateService;

    public function __construct(
        \M2E\TikTokShop\Model\AccountFactory $accountFactory,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Add\Processor $addProcessor,
        \M2E\TikTokShop\Model\Shop\UpdateService $shopUpdateService,
        \M2E\TikTokShop\Model\Warehouse\SynchronizeService $warehouseSynchronizeService,
        \M2E\TikTokShop\Model\ShippingProvider\SynchronizeService $shippingProviderSynchronizeService,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Helper\Magento\Store $storeHelper
    ) {
        $this->addProcessor = $addProcessor;
        $this->warehouseSynchronizeService = $warehouseSynchronizeService;
        $this->accountRepository = $accountRepository;
        $this->accountFactory = $accountFactory;
        $this->storeHelper = $storeHelper;
        $this->shippingProviderSynchronizeService = $shippingProviderSynchronizeService;
        $this->shopUpdateService = $shopUpdateService;
    }

    public function create(string $authCode, string $region): \M2E\TikTokShop\Model\Account
    {
        $response = $this->createOnServer($authCode, $region);

        $existAccount = $this->findExistAccountByOpenId($response->getAccountOpenId());
        if ($existAccount !== null) {
            if ($existAccount->isActive()) {
                throw new \M2E\TikTokShop\Model\Exception(
                    'An account with the same details has already been added. Please make sure you provide unique information.',
                );
            }

            $account = $existAccount;
        } else {
            $account = $this->accountFactory->create();

            $account->init(
                $response->getAccountName(),
                $response->getAccountName(),
                $response->getAccountOpenId(),
                $response->getHash(),
                new \M2E\TikTokShop\Model\Account\Settings\UnmanagedListings(),
                (new \M2E\TikTokShop\Model\Account\Settings\Order())
                    ->createWith(
                        ['listing_other' => ['store_id' => $this->storeHelper->getDefaultStoreId()]],
                    ),
                new \M2E\TikTokShop\Model\Account\Settings\InvoicesAndShipment(),
            );

            $this->accountRepository->create($account);
        }

        $this->shopUpdateService->process($account, $response->getShops());
        $this->synchronizeWarehouses($account);
        $this->synchronizeShippingProvider($account);

        return $account;
    }

    // ----------------------------------------

    private function createOnServer(
        string $authCode,
        string $region
    ): \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Add\Response {
        return $this->addProcessor->process($authCode, $region);
    }

    private function findExistAccountByOpenId(string $openId): ?\M2E\TikTokShop\Model\Account
    {
        return $this->accountRepository->findByOpenId($openId);
    }

    private function synchronizeWarehouses(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($account->getShops() as $shop) {
            $this->warehouseSynchronizeService->sync($account, $shop);
        }
    }

    private function synchronizeShippingProvider(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($account->getShops() as $shop) {
            foreach ($shop->getWarehouses() as $warehouse) {
                $this->shippingProviderSynchronizeService->synchronizeShippingProviders($warehouse);
            }
        }
    }
}
