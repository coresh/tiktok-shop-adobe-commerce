<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Unmanaged\Mapping;

class Unmapping extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $listingOtherRepository;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;
    private \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $mappingService;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $listingOtherRepository,
        \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $mappingService,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingOtherRepository = $listingOtherRepository;
        $this->massActionFilter = $massActionFilter;
        $this->mappingService = $mappingService;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $products = $this->listingOtherRepository->findForUnmappingByMassActionSelectedProducts(
            $this->massActionFilter,
            $accountId
        );

        if (empty($products)) {
            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        $this->mappingService->unMap($products);

        return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
    }
}
