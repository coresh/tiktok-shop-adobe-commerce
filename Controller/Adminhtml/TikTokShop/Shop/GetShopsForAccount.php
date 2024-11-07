<?php

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Shop;

class GetShopsForAccount extends \M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractAccount
{
    private \M2E\TikTokShop\Model\Shop\Repository $shopRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Shop\Repository $shopRepository
    ) {
        parent::__construct();

        $this->shopRepository = $shopRepository;
    }

    public function execute()
    {
        $accountId = $this->getRequest()->getParam('account_id');

        if (empty($accountId)) {
            $this->setJsonContent([
                'result' => false,
                'message' => 'Account Id is required',
            ]);

            return $this->getResult();
        }

        $shops = $this->shopRepository->findForAccount((int)$accountId);
        $shops = array_map(static function (\M2E\TikTokShop\Model\Shop $entity) {
            return [
                'id' => $entity->getId(),
                'shop_name' => $entity->getShopName(),
            ];
        }, $shops);

        $this->setJsonContent([
            'result' => true,
            'shops' => $shops,
        ]);

        return $this->getResult();
    }
}
