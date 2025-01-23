<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\Account;

use M2E\TikTokShop\Model\Account\Issue\ValidTokens;

class DeleteService
{
    private Repository $accountRepository;
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;
    private \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository;
    private \M2E\TikTokShop\Model\Order\Repository $orderRepository;
    private \M2E\TikTokShop\Model\Order\Log\Repository $orderLogRepository;
    private \M2E\TikTokShop\Model\Listing\Log\Repository $listingLogRepository;
    private \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper;
    private \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache;
    private \M2E\TikTokShop\Model\ShippingProvider\Repository $shippingProviderRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\DeleteService $deleteService;
    private \M2E\TikTokShop\Model\Listing\DeleteService $listingDeleteService;
    private \M2E\TikTokShop\Model\Processing\DeleteService $processingDeleteService;
    private \M2E\TikTokShop\Model\Category\Tree\DeleteService $categoryTreeDeleteService;
    private \M2E\TikTokShop\Model\Category\Dictionary\DeleteService $categoryDeleteService;
    private \M2E\TikTokShop\Model\Template\Compliance\Repository $templateComplianceRepository;

    public function __construct(
        Repository $accountRepository,
        \M2E\TikTokShop\Model\Processing\DeleteService $processingDeleteService,
        \M2E\TikTokShop\Model\Listing\DeleteService $listingDeleteService,
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository,
        \M2E\TikTokShop\Model\Warehouse\Repository $warehouseRepository,
        \M2E\TikTokShop\Model\Order\Repository $orderRepository,
        \M2E\TikTokShop\Model\Order\Log\Repository $orderLogRepository,
        \M2E\TikTokShop\Helper\Module\Exception $exceptionHelper,
        \M2E\TikTokShop\Model\Listing\Log\Repository $listingLogRepository,
        \M2E\TikTokShop\Model\ShippingProvider\Repository $shippingProviderRepository,
        \M2E\TikTokShop\Model\UnmanagedProduct\DeleteService $deleteService,
        \M2E\TikTokShop\Helper\Data\Cache\Permanent $cache,
        \M2E\TikTokShop\Model\Category\Tree\DeleteService $categoryTreeDeleteService,
        \M2E\TikTokShop\Model\Category\Dictionary\DeleteService $categoryDeleteService,
        \M2E\TikTokShop\Model\Template\Compliance\Repository $templateComplianceRepository
    ) {
        $this->shopRepository = $shopRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->accountRepository = $accountRepository;
        $this->orderRepository = $orderRepository;
        $this->orderLogRepository = $orderLogRepository;
        $this->listingLogRepository = $listingLogRepository;
        $this->exceptionHelper = $exceptionHelper;
        $this->cache = $cache;
        $this->shippingProviderRepository = $shippingProviderRepository;
        $this->deleteService = $deleteService;
        $this->listingDeleteService = $listingDeleteService;
        $this->processingDeleteService = $processingDeleteService;
        $this->categoryTreeDeleteService = $categoryTreeDeleteService;
        $this->categoryDeleteService = $categoryDeleteService;
        $this->templateComplianceRepository = $templateComplianceRepository;
    }

    /**
     * @param \M2E\TikTokShop\Model\Account $account
     *
     * @return void
     * @throws \Throwable
     */
    public function delete(\M2E\TikTokShop\Model\Account $account): void
    {
        $accountId = $account->getId();

        // ---------------------------------------

        try {
            $this->orderLogRepository->removeByAccountId($accountId);

            $this->orderRepository->removeByAccountId($accountId);

            $this->listingLogRepository->removeByAccountId($accountId);

            $this->deleteService->deleteUnmanagedByAccountId($accountId);

            $this->removeListings($account);

            $this->shippingProviderRepository->removeByAccountId($accountId);

            $this->deleteShops($account);

            $this->templateComplianceRepository->removeByAccountId($accountId);

            $this->deleteAccount($account);
        } catch (\Throwable $e) {
            $this->exceptionHelper->process($e);
            throw $e;
        }
    }

    private function removeListings(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($account->getListings() as $listing) {
            $this->listingDeleteService->process($listing);
        }
    }

    private function deleteShops(\M2E\TikTokShop\Model\Account $account): void
    {
        foreach ($account->getShops() as $shop) {
            $this->categoryDeleteService->deleteByShop($shop);
            $this->categoryTreeDeleteService->deleteByShop($shop);

            $this->warehouseRepository->removeByShopId($shop->getId());

            $this->processingDeleteService->deleteByObjAndObjId(
                \M2E\TikTokShop\Model\Shop::LOCK_NICK,
                $shop->getId()
            );

            $this->shopRepository->remove($shop);
        }
    }

    private function deleteAccount(\M2E\TikTokShop\Model\Account $account): void
    {
        $this->cache->removeTagValues('account');

        $this->accountRepository->remove($account);

        $this->cache->removeValue(ValidTokens::ACCOUNT_TOKENS_CACHE_KEY);
    }
}
