<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Controller\Adminhtml\Product\Unmanaged\Mapping;

class MapProductPopupHtml extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository;

    public function __construct(
        \M2E\TikTokShop\Model\UnmanagedProduct\Repository $unmanagedRepository,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->unmanagedRepository = $unmanagedRepository;
    }

    public function execute()
    {
        $unmanagedId = $this->getRequest()->getParam('other_product_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $unmanagedProduct = $this->unmanagedRepository->findById((int)$unmanagedId);

        $block = $this->getLayout()->createBlock(
            \M2E\TikTokShop\Block\Adminhtml\Listing\Mapping\View::class,
            '',
            [
                'data' => [
                    'other_product_id' => $unmanagedId,
                    'account_id' => $accountId,
                    'grid_url' => '*/product_unmanaged_mapping/mapGrid',
                    'product_type' => $unmanagedProduct->isSimple()
                        ? \M2E\TikTokShop\Helper\Magento\Product::TYPE_SIMPLE
                        : \M2E\TikTokShop\Helper\Magento\Product::TYPE_CONFIGURABLE,
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
