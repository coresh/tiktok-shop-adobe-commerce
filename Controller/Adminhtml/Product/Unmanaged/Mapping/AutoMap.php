<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Unmanaged\Mapping;

class AutoMap extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;
    private \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $mappingService;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\TikTokShop\Model\UnmanagedProduct\MappingService $mappingService,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        $context = null
    ) {
        parent::__construct($context);

        $this->unmanagedRepository = $unmanagedRepository;
        $this->mappingService = $mappingService;
        $this->massActionFilter = $massActionFilter;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $products = $this->unmanagedRepository->findForAutoMappingByMassActionSelectedProducts(
            $this->massActionFilter,
            $accountId
        );

        if (empty($products)) {
            $this->getMessageManager()->addErrorMessage('You should select one or more Products');

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        if (!$this->mappingService->autoMapUnmanagedProducts($products)) {
            $this->getMessageManager()->addErrorMessage(
                'Some Items were not linked. Please edit Product Linking Settings under Configuration > Account > Unmanaged Listings or try to link manually.'
            );

            return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
        }

        return $this->_redirect('*/product_grid/unmanaged/', ['account' => $accountId]);
    }
}
