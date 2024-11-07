<?php

namespace M2E\TikTokShop\Controller\Adminhtml\Listing\Other\Moving;

use M2E\TikTokShop\Model\ResourceModel\Listing\Other as ListingOtherResource;

class PrepareMoveToListing extends \M2E\TikTokShop\Controller\Adminhtml\AbstractListing
{
    private \M2E\TikTokShop\Helper\Data\Session $sessionHelper;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper;
    private \M2E\TikTokShop\Model\Listing\Other\Repository $otherRepository;

    public function __construct(
        \M2E\TikTokShop\Model\Listing\Other\Repository $otherRepository,
        \M2E\TikTokShop\Helper\Data\Session $sessionHelper,
        \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\TikTokShop\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->sessionHelper = $sessionHelper;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->otherRepository = $otherRepository;
    }

    public function execute()
    {
        $isFirstPart = $this->getRequest()->getParam('is_first_part');
        $selectedProductsPart = $this->getRequest()->getParam('products_part');
        $isLastPart = $this->getRequest()->getParam('is_last_part');

        $sessionKey = \M2E\TikTokShop\Helper\View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;

        if ($isFirstPart) {
            $this->sessionHelper->removeValue($sessionKey);
        }

        $selectedProducts = [];
        if ($sessionValue = $this->sessionHelper->getValue($sessionKey)) {
            $selectedProducts = $sessionValue;
        }

        $selectedProductsPart = explode(',', (string)$selectedProductsPart);

        $selectedProducts = array_merge($selectedProducts, $selectedProductsPart);
        $this->sessionHelper->setValue($sessionKey, $selectedProducts);

        if (!$isLastPart) {
            $this->setJsonContent(['result' => true]);

            return $this->getResult();
        }

        $listingOtherCollection = $this->otherRepository->createCollection();
        $listingOtherCollection->addFieldToFilter('id', ['in' => $selectedProducts]);
        $listingOtherCollection->addFieldToFilter(
            \M2E\TikTokShop\Model\ResourceModel\Listing\Other::COLUMN_MAGENTO_PRODUCT_ID,
            ['notnull' => true],
        );

        if ($listingOtherCollection->getSize() != count($selectedProducts)) {
            $this->sessionHelper->removeValue($sessionKey);

            $this->setJsonContent(
                [
                    'result' => false,
                    'message' => __('Only Linked Products must be selected.'),
                ],
            );

            return $this->getResult();
        }

        $listingOtherCollection
            ->getSelect()
            ->join(
                ['cpe' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity')],
                sprintf('%s = cpe.entity_id', ListingOtherResource::COLUMN_MAGENTO_PRODUCT_ID),
            );

        $row = $listingOtherCollection
            ->getSelect()
            ->group(['account_id', 'shop_id'])
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns(['account_id', 'shop_id'])
            ->query()
            ->fetch();

        if ($row !== false) {
            $response = [
                'result' => true,
                'accountId' => (int)$row['account_id'],
                'shopId' => (int)$row['shop_id'],
            ];
        } else {
            $response = [
                'result' => false,
                'message' => __('Magento product not found. Please reload the page.'),
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }
}
