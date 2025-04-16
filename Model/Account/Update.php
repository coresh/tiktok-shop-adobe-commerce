<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Account;

use M2E\TikTokShop\Model\Account\Issue\ValidTokens;

class Update
{
    private \M2E\TikTokShop\Model\Warehouse\SynchronizeService $warehouseSynchronizeService;
    private \M2E\TikTokShop\Model\ShippingProvider\SynchronizeService $shippingProviderSynchronizeService;
    private \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Update\Processor $updateProcessor;
    private \M2E\TikTokShop\Model\Shop\UpdateService $shopUpdateService;
    private \M2E\TikTokShop\Model\Account\Repository $accountRepository;
    private \M2E\TikTokShop\Model\Warehouse\ShippingMappingUpdater $shippingMappingUpdater;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;

    public function __construct(
        \M2E\TikTokShop\Model\Warehouse\ShippingMappingUpdater $shippingMappingUpdater,
        \M2E\TikTokShop\Model\TikTokShop\Connector\Account\Update\Processor $updateProcessor,
        \M2E\TikTokShop\Model\Shop\UpdateService $shopUpdateService,
        \M2E\TikTokShop\Model\Warehouse\SynchronizeService $warehouseSynchronizeService,
        \M2E\TikTokShop\Model\ShippingProvider\SynchronizeService $shippingProviderSynchronizeService,
        \M2E\TikTokShop\Model\Account\Repository $accountRepository,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache
    ) {
        $this->warehouseSynchronizeService = $warehouseSynchronizeService;
        $this->updateProcessor = $updateProcessor;
        $this->shippingProviderSynchronizeService = $shippingProviderSynchronizeService;
        $this->shopUpdateService = $shopUpdateService;
        $this->accountRepository = $accountRepository;
        $this->shippingMappingUpdater = $shippingMappingUpdater;
        $this->cache = $cache;
    }

    public function updateSettings(
        \M2E\TikTokShop\Model\Account $account,
        string $title,
        \M2E\TikTokShop\Model\Account\Settings\UnmanagedListings $unmanagedListingsSettings,
        \M2E\TikTokShop\Model\Account\Settings\Order $orderSettings,
        \M2E\TikTokShop\Model\Account\Settings\InvoicesAndShipment $invoicesAndShipmentSettings,
        array $shippingProviderMapping
    ): void {
        $account->setTitle($title)
            ->setUnmanagedListingSettings($unmanagedListingsSettings)
            ->setOrdersSettings($orderSettings)
            ->setInvoiceAndShipmentSettings($invoicesAndShipmentSettings);

        $this->accountRepository->save($account);

        foreach ($shippingProviderMapping as $warehouseId => $data) {
            $this->shippingMappingUpdater->update((string)$warehouseId, $data);
        }
    }

    public function updateCredentials(\M2E\TikTokShop\Model\Account $account, string $authCode): void
    {
        $response = $this->updateProcessor->process($account, $authCode);

        $this->shopUpdateService->process($account, $response->getShops());

        $sellerName = $response->getSellerName();
        $account->setSellerName($sellerName);
        $this->accountRepository->save($account);

        $this->synchronizeWarehouses($account);
        $this->synchronizeShippingProviders($account);

        $this->cache->removeValue(ValidTokens::ACCOUNT_TOKENS_CACHE_KEY);
    }

    public function refresh(\M2E\TikTokShop\Model\Account $account): void
    {
        if (empty($account->getShops())) {
            throw new \M2E\TikTokShop\Model\Exception('Shops not found');
        }

        $this->synchronizeWarehouses($account);
        $this->synchronizeShippingProviders($account);
    }

    private function synchronizeWarehouses(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($account->getShops() as $shop) {
            $this->warehouseSynchronizeService->sync($account, $shop);
        }
    }

    private function synchronizeShippingProviders(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($account->getShops() as $shop) {
            foreach ($shop->getWarehouses() as $warehouse) {
                $this->shippingProviderSynchronizeService->synchronizeShippingProviders($warehouse);
            }
        }
    }
}
