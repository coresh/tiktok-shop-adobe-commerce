<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\TikTokShop\Listing\Edit;

use M2E\TikTokShop\Controller\Adminhtml\TikTokShop\AbstractMain;

class SaveWarehouse extends AbstractMain implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    private \M2E\TikTokShop\Model\Listing\Repository $listingRepository;
    private \M2E\TikTokShop\Model\TikTokShop\Listing\ChangeWarehouseService $changeWarehouseService;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Repository $listingRepository,
        \M2E\TikTokShop\Model\TikTokShop\Listing\ChangeWarehouseService $changeWarehouseService,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->listingRepository = $listingRepository;
        $this->changeWarehouseService = $changeWarehouseService;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['id']) || empty($params['warehouse_id'])) {
            return $this->getResponse()->setBody(__('You should provide correct parameters.'));
        }

        $listingId = (int)$params['id'];
        $listing = $this->listingRepository->get($listingId);
        $this->changeWarehouseService->change($listing, (int)$params['warehouse_id']);

        return $this->getResult();
    }
}
