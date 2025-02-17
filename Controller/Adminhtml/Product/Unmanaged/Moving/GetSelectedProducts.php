<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Unmanaged\Moving;

class GetSelectedProducts extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory;
    private \Magento\Ui\Component\MassAction\Filter $massActionFilter;
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $massActionFilter,
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->massActionFilter = $massActionFilter;
        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        $products = $this->unmanagedRepository->findForMovingByMassActionSelectedProducts(
            $this->massActionFilter,
            $accountId
        );
        $ids = [];
        foreach ($products as $product) {
            $ids[] = (int)$product->getId();
        }

        $response = [
            'selected_products' => $ids,
        ];

        if (empty($ids)) {
            $response['message'] = \__('Only Linked Products must be selected.');
        }

        return $this->resultJsonFactory->create()
                                       ->setData($response);
    }
}
